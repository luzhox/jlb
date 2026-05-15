/**
 * onScroll.js — Animaciones GSAP ScrollTrigger
 *
 * Reemplaza AOS progresivamente. Los elementos con data-gsap="fade-up"
 * usan GSAP. Los elementos con data-aos="fade-up" siguen siendo
 * animados por AOS (compatibilidad con templates existentes).
 *
 * Uso en PHP:
 *   <div data-gsap="fade-up">...</div>
 *   <div data-gsap="fade-right" data-gsap-delay="0.2">...</div>
 */
import { gsap, ScrollTrigger } from './gsap.js'

const ANIMATIONS = {
  'fade-up':    { y: 50, opacity: 0 },
  'fade-down':  { y: -50, opacity: 0 },
  'fade-right': { x: -50, opacity: 0 },
  'fade-left':  { x: 50, opacity: 0 },
  'zoom-in':    { scale: 0.85, opacity: 0 },
  'zoom-out':   { scale: 1.15, opacity: 0 },
}

/**
 * Animar elementos con data-gsap="*"
 */
function animateElements() {
  const elements = document.querySelectorAll('[data-gsap]')
  if (!elements.length) return

  elements.forEach((el) => {
    const type    = el.dataset.gsap || 'fade-up'
    const delay   = parseFloat(el.dataset.gsapDelay || 0)
    const duration = parseFloat(el.dataset.gsapDuration || 0.7)
    const from    = ANIMATIONS[type] || ANIMATIONS['fade-up']

    gsap.from(el, {
      ...from,
      duration,
      delay,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 85%',
        toggleActions: 'play none none none',
      },
    })
  })
}

/**
 * Stagger batch — animar grupos de cards/items con retraso entre elementos.
 * Uso: <ul data-gsap-batch=".card">
 */
function animateBatches() {
  const batches = document.querySelectorAll('[data-gsap-batch]')
  if (!batches.length) return

  batches.forEach((container) => {
    const selector = container.dataset.gsapBatch || '.card'
    const items    = container.querySelectorAll(selector)
    if (!items.length) return

    ScrollTrigger.batch(items, {
      onEnter: (elements) => {
        gsap.from(elements, {
          opacity: 0,
          y: 40,
          duration: 0.6,
          stagger: 0.08,
          ease: 'power2.out',
        })
      },
      start: 'top 85%',
    })
  })
}

/**
 * Parallax — fondo con movimiento lento al hacer scroll.
 * Uso: <div data-gsap-parallax data-gsap-speed="0.3">
 */
function animateParallax() {
  const elements = document.querySelectorAll('[data-gsap-parallax]')
  if (!elements.length) return

  elements.forEach((el) => {
    const speed = parseFloat(el.dataset.gsapSpeed || 0.2)

    gsap.to(el, {
      yPercent: speed * -100,
      ease: 'none',
      scrollTrigger: {
        trigger: el.parentElement || el,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
      },
    })
  })
}

/**
 * Contador animado — anima un número del 0 al valor del texto.
 * Uso: <span data-gsap-counter>250</span>
 */
function animateCounters() {
  const counters = document.querySelectorAll('[data-gsap-counter]')
  if (!counters.length) return

  counters.forEach((el) => {
    const target = parseInt(el.textContent, 10)
    if (isNaN(target)) return

    const obj = { value: 0 }

    gsap.to(obj, {
      value: target,
      duration: 2.5,
      ease: 'power2.out',
      onUpdate: () => { el.textContent = Math.ceil(obj.value).toLocaleString() },
      scrollTrigger: {
        trigger: el,
        start: 'top 80%',
        toggleActions: 'play none none none',
      },
    })
  })
}

/**
 * Punto de entrada principal
 */
export function initScrollAnimations() {
  animateElements()
  animateBatches()
  animateParallax()
  animateCounters()

  // Refrescar ScrollTrigger al redimensionar (imágenes lazy, etc.)
  ScrollTrigger.refresh()
}

export { gsap, ScrollTrigger }
