/**
 * jlbFaq.js — Acordeón del módulo jlb_faq. Patrón disclosure accesible:
 * un panel abierto a la vez, toggle de `hidden` + `aria-expanded`.
 */
function initJlbFaq() {
    document.querySelectorAll('[data-jlb-faq]').forEach((list) => {
        const buttons = Array.from(list.querySelectorAll('.jlb-faq__q'))
        if (!buttons.length) return

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const isOpen = btn.getAttribute('aria-expanded') === 'true'
                // Cierra todos (uno abierto a la vez).
                buttons.forEach((b) => {
                    b.setAttribute('aria-expanded', 'false')
                    const panel = document.getElementById(b.getAttribute('aria-controls'))
                    if (panel) panel.hidden = true
                })
                if (!isOpen) {
                    btn.setAttribute('aria-expanded', 'true')
                    const panel = document.getElementById(btn.getAttribute('aria-controls'))
                    if (panel) panel.hidden = false
                }
            })
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbFaq)
} else {
    initJlbFaq()
}

export { initJlbFaq }
