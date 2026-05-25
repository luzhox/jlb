/**
 * jlbOpenDayForm.js — Formulario Open Day (módulo jlb_open_day_form).
 *
 * Validación en cliente (campos requeridos, email, consentimientos) + envío
 * asíncrono al endpoint REST `jlb/v1/open-day` (proxy a HubSpot, ver
 * inc/hubspot.php). Muestra estado accesible (aria-live) y marca campos con error.
 */

function initJlbOpenDay() {
    document.querySelectorAll('[data-jlb-openday]').forEach((form) => {
        const endpoint = form.dataset.endpoint
        const nonce = form.dataset.nonce
        const statusEl = form.querySelector('[data-jlb-openday-status]')
        const submitBtn = form.querySelector('.jlb-openday__submit')
        if (!endpoint) return

        const setStatus = (msg, type) => {
            if (!statusEl) return
            statusEl.textContent = msg
            statusEl.hidden = !msg
            statusEl.dataset.type = type || ''
        }

        const markError = (name, on) => {
            const field = form.querySelector(`[name="${name}"]`)
            const wrap = field ? field.closest('.jlb-openday__field, .jlb-openday__check') : null
            if (wrap) wrap.classList.toggle('has-error', !!on)
        }

        const clearErrors = () => {
            form.querySelectorAll('.has-error').forEach((el) => el.classList.remove('has-error'))
        }

        const validate = () => {
            const errs = []
            const val = (n) => (form.querySelector(`[name="${n}"]`)?.value || '').trim()
            const requiredText = ['responsable', 'postulante', 'correo', 'celular', 'grado']
            requiredText.forEach((n) => { if (!val(n)) errs.push(n) })

            const email = val('correo')
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errs.push('correo')

            if (!form.querySelector('[name="anio_admision"]:checked')) errs.push('anio_admision')
            if (!form.querySelector('[name="hora_visita"]:checked')) errs.push('hora_visita')
            if (!form.querySelector('[name="consent_datos"]')?.checked) errs.push('consent_datos')
            if (!form.querySelector('[name="consent_info"]')?.checked) errs.push('consent_info')
            return [...new Set(errs)]
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault()
            clearErrors()
            const errs = validate()
            if (errs.length) {
                errs.forEach((n) => markError(n, true))
                setStatus('Revisa los campos marcados.', 'error')
                form.querySelector(`[name="${errs[0]}"]`)?.focus()
                return
            }

            submitBtn && (submitBtn.disabled = true)
            setStatus('Enviando…', 'pending')

            try {
                const body = new URLSearchParams(new FormData(form))
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': nonce, 'Accept': 'application/json' },
                    body,
                })
                const data = await res.json().catch(() => ({}))

                if (res.ok && data.ok) {
                    form.reset()
                    if (window.grecaptcha && typeof window.grecaptcha.reset === 'function') window.grecaptcha.reset()
                    setStatus(data.message || '¡Gracias! Tu registro fue recibido.', 'success')
                } else {
                    if (data.errors) Object.keys(data.errors).forEach((n) => markError(n, true))
                    setStatus(data.message || 'No pudimos enviar tu registro. Inténtalo más tarde.', 'error')
                }
            } catch (err) {
                setStatus('Error de conexión. Revisa tu red e inténtalo de nuevo.', 'error')
            } finally {
                submitBtn && (submitBtn.disabled = false)
            }
        })

        // Limpia el error de un campo al corregirlo.
        form.addEventListener('input', (e) => {
            const wrap = e.target.closest('.jlb-openday__field, .jlb-openday__check')
            if (wrap) wrap.classList.remove('has-error')
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbOpenDay)
} else {
    initJlbOpenDay()
}

export { initJlbOpenDay }
