/**
 * jlbCuota.js — Calculadora del módulo jlb_cuota.
 * Nivel de postulante × Modo de pago → Resultado (+ "Ahorras $X" si es al
 * contado). Datos en data-* de cada <option> de nivel. Se recalcula al cambiar
 * cualquier select y al pulsar "Calcular"; el resultado se anuncia (aria-live).
 */
function initJlbCuota() {
    document.querySelectorAll('[data-jlb-cuota]').forEach((form) => {
        const nivel = form.querySelector('[data-jlb-cuota-nivel]')
        const modo = form.querySelector('[data-jlb-cuota-modo]')
        const resOut = form.querySelector('[data-jlb-cuota-resultado]')
        const ahorroOut = form.querySelector('[data-jlb-cuota-ahorro]')
        const btn = form.querySelector('[data-jlb-cuota-calc]')
        if (!nivel) return

        const apply = () => {
            const opt = nivel.options[nivel.selectedIndex]
            if (!opt) return
            const m = modo ? modo.value : 'contado'
            const val = m === 'cuotas' ? opt.dataset.cuotas : opt.dataset.contado
            if (resOut) resOut.textContent = val || ''
            if (ahorroOut) {
                const ah = opt.dataset.ahorro
                if (m === 'contado' && ah) {
                    ahorroOut.textContent = 'Ahorras ' + ah
                    ahorroOut.hidden = false
                } else {
                    ahorroOut.hidden = true
                }
            }
        }

        nivel.addEventListener('change', apply)
        if (modo) modo.addEventListener('change', apply)
        if (btn) btn.addEventListener('click', apply)
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbCuota)
} else {
    initJlbCuota()
}

export { initJlbCuota }
