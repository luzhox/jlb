/**
 * jlbProceso.js — Stepper/tabs del módulo jlb_proceso (proceso de admisión).
 * Patrón ARIA tabs: click + flechas/Home/End, roving tabindex. En mobile las
 * pestañas se apilan sobre el panel (mismo markup, lo resuelve el CSS).
 */
function initJlbProceso() {
    document.querySelectorAll('[data-jlb-tabs]').forEach((stepper) => {
        const tabs = Array.from(stepper.querySelectorAll('[data-jlb-tab]'))
        const panels = Array.from(stepper.querySelectorAll('[data-jlb-panel]'))
        if (tabs.length < 2) return

        const activate = (idx, focus = false) => {
            tabs.forEach((t, i) => {
                const on = i === idx
                t.classList.toggle('is-active', on)
                t.setAttribute('aria-selected', on ? 'true' : 'false')
                t.tabIndex = on ? 0 : -1
                if (on && focus) t.focus()
            })
            panels.forEach((p, i) => {
                const on = i === idx
                p.classList.toggle('is-active', on)
                if (on) p.removeAttribute('hidden')
                else p.setAttribute('hidden', '')
            })
        }

        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => activate(i))
            tab.addEventListener('keydown', (e) => {
                let ni = null
                if (e.key === 'ArrowDown' || e.key === 'ArrowRight') ni = (i + 1) % tabs.length
                else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') ni = (i - 1 + tabs.length) % tabs.length
                else if (e.key === 'Home') ni = 0
                else if (e.key === 'End') ni = tabs.length - 1
                if (ni !== null) {
                    e.preventDefault()
                    activate(ni, true)
                }
            })
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbProceso)
} else {
    initJlbProceso()
}

export { initJlbProceso }
