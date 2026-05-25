/**
 * jlbManifesto.js — Gradiente progresivo en las palabras <strong> del manifesto.
 *
 * En Figma el gradiente (morado → vino → rojo) NO es por palabra, sino que
 * cruza TODO el párrafo: las palabras bold de arriba son moradas y las de abajo
 * rojas. Con CSS puro cada <strong> tendría su propio gradiente (se ve plano).
 *
 * Solución: cada <strong> comparte un único gradiente del tamaño del párrafo,
 * desplazando su background-position según su posición vertical dentro del
 * párrafo (background-clip:text recorta la porción que le toca).
 */

function paintManifesto() {
    document.querySelectorAll('.jlb-manifesto p').forEach((p) => {
        const strongs = p.querySelectorAll('strong')
        if (!strongs.length) return
        const ph = p.offsetHeight
        if (!ph) return
        const pTop = p.getBoundingClientRect().top
        strongs.forEach((s) => {
            const top = s.getBoundingClientRect().top - pTop
            s.style.backgroundSize = `100% ${ph}px`
            s.style.backgroundPosition = `0 ${-top}px`
        })
    })
}

function initJlbManifesto() {
    if (!document.querySelector('.jlb-manifesto strong')) return
    paintManifesto()

    // Recalcular al cambiar el ancho (el wrap del párrafo cambia las posiciones).
    let t
    window.addEventListener('resize', () => {
        clearTimeout(t)
        t = setTimeout(paintManifesto, 120)
    })

    // Las fuentes custom (DM Sans) pueden cambiar la altura al cargar → repintar.
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(paintManifesto)
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbManifesto)
} else {
    initJlbManifesto()
}

export { initJlbManifesto, paintManifesto }
