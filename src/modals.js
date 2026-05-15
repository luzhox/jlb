// modals.js — Vanilla JS + accesibilidad (focus trap, Escape, retorno de foco)
document.addEventListener('DOMContentLoaded', () => {
    let lastFocused = null

    function openModal(modalId) {
        const modal = document.getElementById(modalId)
        if (!modal) return

        lastFocused = document.activeElement
        modal.classList.add('active')
        modal.setAttribute('aria-hidden', 'false')

        // Mover foco al primer elemento interactivo del modal
        const focusable = modal.querySelectorAll(
            'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        )
        if (focusable.length) focusable[0].focus()
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId)
        if (!modal) return

        modal.classList.remove('active')
        modal.setAttribute('aria-hidden', 'true')

        // Devolver foco al elemento que abrió el modal
        if (lastFocused) lastFocused.focus()
    }

    // Cerrar con botón .close — obtener modal del data-modal del botón
    document.addEventListener('click', e => {
        const closeBtn = e.target.closest('.close')
        if (closeBtn) {
            const modalId = closeBtn.dataset.modal
            if (modalId) closeModal(modalId)
        }
    })

    // Cerrar al hacer click en el overlay de un modal activo
    document.addEventListener('click', e => {
        const overlay = e.target.closest('.modal.active .overlay')
        if (overlay) {
            const modal = overlay.closest('.modal')
            if (modal) closeModal(modal.id)
        }
    })

    // Cerrar con tecla Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active')
            if (activeModal) closeModal(activeModal.id)
        }
    })
})
