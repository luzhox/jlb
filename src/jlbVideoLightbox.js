/**
 * jlbVideoLightbox.js — Lightbox de video para el template JLB.
 *
 * Cualquier elemento con `data-jlb-video="<url>"` abre el video en un overlay
 * modal accesible (focus trap, Escape, click en backdrop, retorno de foco).
 *
 * Soporta:
 *   · YouTube  (youtube.com/watch?v=, youtu.be/, /embed/, /shorts/)
 *   · Vimeo    (vimeo.com/ID, player.vimeo.com/video/ID)
 *   · Archivos (.mp4 / .webm / .ogg) → <video> nativo con controles.
 *
 * Progressive enhancement: los triggers <a href="<url>"> siguen funcionando sin
 * JS (abren el video en una pestaña). El JS hace preventDefault y abre el modal.
 */

const FOCUSABLE = 'a[href], button:not([disabled]), iframe, video, [tabindex]:not([tabindex="-1"])'

/** Devuelve { type: 'iframe'|'video', src } a partir de una URL de video. */
function resolveVideo(url) {
    if (!url) return null
    const clean = url.trim()

    // YouTube
    const yt =
        clean.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/)
    if (yt) {
        return {
            type: 'iframe',
            src: `https://www.youtube-nocookie.com/embed/${yt[1]}?autoplay=1&rel=0`,
        }
    }

    // Vimeo
    const vm = clean.match(/vimeo\.com\/(?:video\/)?(\d+)/)
    if (vm) {
        return { type: 'iframe', src: `https://player.vimeo.com/video/${vm[1]}?autoplay=1` }
    }

    // Archivo de video directo
    if (/\.(mp4|webm|ogg)(\?.*)?$/i.test(clean)) {
        return { type: 'video', src: clean }
    }

    // Fallback: intentar como iframe genérico (embed ya formado)
    return { type: 'iframe', src: clean }
}

function initJlbVideoLightbox() {
    if (document.querySelector('.jlb-video-lightbox')) return // ya inicializado

    let lastFocused = null

    // ── Markup del overlay (una sola instancia, reutilizada) ──
    const overlay = document.createElement('div')
    overlay.className = 'jlb-video-lightbox'
    overlay.setAttribute('role', 'dialog')
    overlay.setAttribute('aria-modal', 'true')
    overlay.setAttribute('aria-label', 'Reproductor de video')
    overlay.hidden = true
    overlay.innerHTML = `
        <div class="jlb-video-lightbox__backdrop" data-jlb-video-close></div>
        <div class="jlb-video-lightbox__dialog" role="document">
            <button type="button" class="jlb-video-lightbox__close" aria-label="Cerrar video" data-jlb-video-close>
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="jlb-video-lightbox__frame"></div>
        </div>`
    document.body.appendChild(overlay)

    const frame = overlay.querySelector('.jlb-video-lightbox__frame')
    const closeBtn = overlay.querySelector('.jlb-video-lightbox__close')

    function open(url) {
        const v = resolveVideo(url)
        if (!v) return

        if (v.type === 'iframe') {
            frame.innerHTML = `<iframe src="${v.src}" title="Video" frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture; encrypted-media"
                allowfullscreen></iframe>`
        } else {
            frame.innerHTML = `<video src="${v.src}" controls autoplay playsinline></video>`
        }

        lastFocused = document.activeElement
        overlay.hidden = false
        // Forzar reflow para la transición de entrada
        // eslint-disable-next-line no-unused-expressions
        overlay.offsetHeight
        overlay.classList.add('is-open')
        document.documentElement.classList.add('jlb-video-lightbox-open')
        closeBtn.focus()
    }

    function close() {
        overlay.classList.remove('is-open')
        document.documentElement.classList.remove('jlb-video-lightbox-open')
        // Limpiar el src corta la reproducción (audio incluido)
        const onHide = () => {
            overlay.hidden = true
            frame.innerHTML = ''
            overlay.removeEventListener('transitionend', onHide)
        }
        overlay.addEventListener('transitionend', onHide)
        // Fallback si no hay transición
        setTimeout(() => { if (!overlay.classList.contains('is-open')) onHide() }, 320)
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus()
    }

    // ── Trigger: cualquier [data-jlb-video] ──
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-jlb-video]')
        if (trigger) {
            const url = trigger.getAttribute('data-jlb-video') || trigger.getAttribute('href')
            if (url && url !== '#') {
                e.preventDefault()
                open(url)
            }
            return
        }
        if (e.target.closest('[data-jlb-video-close]')) close()
    })

    // ── Escape + focus trap ──
    document.addEventListener('keydown', (e) => {
        if (overlay.hidden) return
        if (e.key === 'Escape') {
            e.preventDefault()
            close()
            return
        }
        if (e.key === 'Tab') {
            const items = overlay.querySelectorAll(FOCUSABLE)
            if (!items.length) return
            const first = items[0]
            const last = items[items.length - 1]
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault()
                last.focus()
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault()
                first.focus()
            }
        }
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbVideoLightbox)
} else {
    initJlbVideoLightbox()
}

export { initJlbVideoLightbox }
