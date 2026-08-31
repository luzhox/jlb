/**
 * Re-scroll al ancla tras cargar la página completa.
 *
 * Al navegar entre páginas con hash (p.ej. /admision/#admision-form desde
 * "Contáctanos"), el navegador scrollea al ancla ANTES de que carguen las
 * imágenes de los módulos superiores; cuando terminan, empujan el destino
 * hacia abajo y el usuario queda a mitad de página. Repetimos el scroll en
 * `load` (todas las imágenes resueltas) para aterrizar donde corresponde.
 * El scroll-margin-top de cada sección (header sticky) se respeta solo.
 */
window.addEventListener('load', () => {
  const hash = window.location.hash
  if (!hash || hash.length < 2) return

  let target
  try {
    target = document.querySelector(hash)
  } catch {
    return // hash no es un selector válido (p.ej. #/ruta de apps)
  }
  if (!target) return

  // Solo si el ancla quedó desplazada (evita pisar un scroll manual del usuario).
  const rect = target.getBoundingClientRect()
  const offBelow = rect.top > window.innerHeight
  const offAbove = rect.bottom < 0
  if (offBelow || offAbove) {
    target.scrollIntoView({ behavior: 'auto' })
  }
})
