// acordeon.js — Acordeón accesible (Vanilla JS)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.acordeon__pregunta').forEach(btn => {
        btn.addEventListener('click', () => {
            const isOpen   = btn.getAttribute('aria-expanded') === 'true'
            const targetId = btn.getAttribute('aria-controls')
            const panel    = document.getElementById(targetId)
            if (!panel) return

            btn.setAttribute('aria-expanded', String(!isOpen))

            if (isOpen) {
                panel.setAttribute('hidden', '')
            } else {
                panel.removeAttribute('hidden')
            }
        })
    })
})
