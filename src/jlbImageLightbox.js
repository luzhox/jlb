/**
 * jlbImageLightbox.js — Lightbox de imagen (zoom al clic) para el template JLB.
 *
 * Cualquier elemento con `data-jlb-zoom="<url>"` abre la imagen a tamaño
 * completo en un overlay modal accesible (focus trap, Escape, click en backdrop,
 * retorno de foco). Si varios triggers comparten `data-jlb-zoom-group="<id>"`,
 * el overlay habilita navegación prev/next (flechas y teclas ←/→) entre ellos.
 *
 * Progressive enhancement: los triggers <a href="<url>"> siguen funcionando sin
 * JS (abren la imagen directa). El JS hace preventDefault y abre el modal.
 *
 * Patrón espejo de jlbVideoLightbox.js.
 */

const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'

function initJlbImageLightbox() {
    if (document.querySelector('.jlb-image-lightbox')) return // ya inicializado

    let lastFocused = null
    let items = []   // [{ src, alt }]
    let index = 0

    // ── Markup del overlay (una sola instancia, reutilizada) ──
    const overlay = document.createElement('div')
    overlay.className = 'jlb-image-lightbox'
    overlay.setAttribute('role', 'dialog')
    overlay.setAttribute('aria-modal', 'true')
    overlay.setAttribute('aria-label', 'Imagen ampliada')
    overlay.hidden = true
    overlay.innerHTML = `
        <div class="jlb-image-lightbox__backdrop" data-jlb-zoom-close></div>
        <div class="jlb-image-lightbox__dialog" role="document">
            <button type="button" class="jlb-image-lightbox__close" aria-label="Cerrar" data-jlb-zoom-close>
                <span aria-hidden="true">&times;</span>
            </button>
            <button type="button" class="jlb-image-lightbox__nav jlb-image-lightbox__nav--prev" aria-label="Imagen anterior" data-jlb-zoom-prev>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <figure class="jlb-image-lightbox__frame">
                <img alt="" data-jlb-zoom-img>
                <figcaption class="jlb-image-lightbox__caption" data-jlb-zoom-caption></figcaption>
            </figure>
            <button type="button" class="jlb-image-lightbox__nav jlb-image-lightbox__nav--next" aria-label="Imagen siguiente" data-jlb-zoom-next>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>`
    document.body.appendChild(overlay)

    const img = overlay.querySelector('[data-jlb-zoom-img]')
    const caption = overlay.querySelector('[data-jlb-zoom-caption]')
    const closeBtn = overlay.querySelector('.jlb-image-lightbox__close')
    const prevBtn = overlay.querySelector('[data-jlb-zoom-prev]')
    const nextBtn = overlay.querySelector('[data-jlb-zoom-next]')

    function render() {
        const it = items[index]
        if (!it) return
        img.src = it.src
        img.alt = it.alt || ''
        caption.textContent = it.alt || ''
        caption.hidden = !it.alt
        const multi = items.length > 1
        prevBtn.hidden = !multi
        nextBtn.hidden = !multi
    }

    function go(delta) {
        if (items.length < 2) return
        index = (index + delta + items.length) % items.length
        render()
    }

    function open(list, start) {
        items = list
        index = start
        render()
        lastFocused = document.activeElement
        overlay.hidden = false
        // Forzar reflow para la transición de entrada
        // eslint-disable-next-line no-unused-expressions
        overlay.offsetHeight
        overlay.classList.add('is-open')
        document.documentElement.classList.add('jlb-image-lightbox-open')
        closeBtn.focus()
    }

    function close() {
        overlay.classList.remove('is-open')
        document.documentElement.classList.remove('jlb-image-lightbox-open')
        const onHide = () => {
            overlay.hidden = true
            img.src = ''
            overlay.removeEventListener('transitionend', onHide)
        }
        overlay.addEventListener('transitionend', onHide)
        setTimeout(() => { if (!overlay.classList.contains('is-open')) onHide() }, 320)
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus()
    }

    // ── Trigger: cualquier [data-jlb-zoom] ──
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-jlb-zoom]')
        if (trigger) {
            const url = trigger.getAttribute('data-jlb-zoom') || trigger.getAttribute('href')
            if (!url || url === '#') return
            e.preventDefault()
            const group = trigger.getAttribute('data-jlb-zoom-group')
            const triggers = group
                ? Array.from(document.querySelectorAll(`[data-jlb-zoom-group="${CSS.escape(group)}"]`))
                : [trigger]
            const list = triggers.map((el) => ({
                src: el.getAttribute('data-jlb-zoom') || el.getAttribute('href'),
                alt: el.getAttribute('data-jlb-zoom-alt') || el.querySelector('img')?.alt || '',
            }))
            open(list, Math.max(0, triggers.indexOf(trigger)))
            return
        }
        if (e.target.closest('[data-jlb-zoom-close]')) { close(); return }
        if (e.target.closest('[data-jlb-zoom-prev]')) { go(-1); return }
        if (e.target.closest('[data-jlb-zoom-next]')) { go(1) }
    })

    // ── Escape + flechas + focus trap ──
    document.addEventListener('keydown', (e) => {
        if (overlay.hidden) return
        if (e.key === 'Escape') { e.preventDefault(); close(); return }
        if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); return }
        if (e.key === 'ArrowRight') { e.preventDefault(); go(1); return }
        if (e.key === 'Tab') {
            const focusables = Array.from(overlay.querySelectorAll(FOCUSABLE)).filter((el) => !el.hidden && el.offsetParent !== null)
            if (!focusables.length) return
            const first = focusables[0]
            const last = focusables[focusables.length - 1]
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
    document.addEventListener('DOMContentLoaded', initJlbImageLightbox)
} else {
    initJlbImageLightbox()
}

export { initJlbImageLightbox }
