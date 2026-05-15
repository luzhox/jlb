// inputFields.js — Vanilla JS
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.input-field').forEach(field => {
        field.addEventListener('focusin', () => {
            field.classList.add('active')
        })

        field.addEventListener('focusout', () => {
            const input = field.querySelector('.wpcf7-form-control-wrap input')
            if (input && input.value.length > 0) {
                field.classList.add('active')
            } else {
                field.classList.remove('active')
            }
        })
    })
})
