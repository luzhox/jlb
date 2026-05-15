/**
 * Footer watermark resizer
 *
 * Mide el SVG <text> y ajusta font-size + viewBox para que el texto ocupe
 * el ancho del contenedor sin overflow horizontal. Espera a que las webfonts
 * estén disponibles (document.fonts.ready) para evitar saltos al cambiar
 * de fallback a DM Sans.
 *
 * Resize handler con debounce 150ms (recomendación SEO/performance: evita
 * thrashing de getBBox en orientaciones móviles cambiantes).
 */

const DEBOUNCE_MS = 150

function debounce(fn, wait) {
    let t
    return function (...args) {
        clearTimeout(t)
        t = setTimeout(() => fn.apply(this, args), wait)
    }
}

function fitWatermark(container) {
    const svg  = container.querySelector('svg.footer-watermark__svg')
    const text = container.querySelector('[data-bp-watermark-text]')
    if (!svg || !text) return

    // Mide el contenedor
    const containerWidth = container.clientWidth
    if (!containerWidth) return

    // Reset font-size base para mediar cleanly
    text.setAttribute('font-size', '100')
    let bbox
    try {
        bbox = text.getBBox()
    } catch (e) {
        return // SVG no rendered yet
    }
    if (!bbox || !bbox.width) return

    // Ratio: target = container width, current = bbox.width @ font-size 100
    const ratio = (containerWidth / bbox.width) * 100
    const finalSize = Math.max(80, Math.min(ratio, 1200))

    text.setAttribute('font-size', String(finalSize))

    // Recalcular bbox final para ajustar viewBox y altura visual
    let finalBox
    try {
        finalBox = text.getBBox()
    } catch (e) {
        return
    }

    // Ajusta viewBox al ancho/alto reales del texto, eliminando padding lateral.
    // Margen vertical pequeño (10%) para que descenders no se corten.
    const padY = finalBox.height * 0.10
    svg.setAttribute(
        'viewBox',
        `${finalBox.x} ${finalBox.y - padY} ${finalBox.width} ${finalBox.height + padY * 2}`
    )
}

function fitAll() {
    document.querySelectorAll('[data-bp-watermark]').forEach(fitWatermark)
}

function init() {
    if (!document.querySelector('[data-bp-watermark]')) return

    // Primera medida cuando las fonts estén listas (evita salto FOUT)
    if (document.fonts && typeof document.fonts.ready?.then === 'function') {
        document.fonts.ready.then(fitAll)
    } else {
        fitAll()
    }

    // Re-fit al resize con debounce
    window.addEventListener('resize', debounce(fitAll, DEBOUNCE_MS), { passive: true })

    // Re-fit cuando una imagen tardía o el lazy del vídeo cambie el layout
    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(debounce(fitAll, DEBOUNCE_MS))
        document.querySelectorAll('[data-bp-watermark]').forEach((el) => ro.observe(el))
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true })
} else {
    init()
}
