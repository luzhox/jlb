/**
 * onScroll.js — Animaciones GSAP ScrollTrigger.
 *
 * Reemplaza AOS progresivamente. Los elementos con data-gsap="fade-up"
 * (o data-gsap-batch / data-gsap-parallax / data-gsap-counter) son
 * detectados y animados aquí; AOS sigue cubriendo los data-aos legacy.
 *
 * Uso en PHP:
 *   <div data-gsap="fade-up">…</div>
 *   <div data-gsap="fade-right" data-gsap-delay="0.2">…</div>
 *   <div data-gsap-batch=".card">…</div>
 *   <span data-gsap-counter>250</span>
 *
 * Notas de implementación:
 *   · `immediateRender: true` en gsap.from() evita el "flash" (los elementos
 *     son visibles en su estado final un frame antes de que ScrollTrigger
 *     dispare → GSAP los snappea al estado inicial y los anima). Con este
 *     flag, GSAP fija el estado inicial al construir la animación.
 *   · prefers-reduced-motion: si el SO/navegador lo pide, salimos sin animar
 *     y forzamos que cualquier estilo CSS de "estado inicial oculto" se
 *     resetee (la regla CSS está en src/main.css).
 */
import { gsap, ScrollTrigger } from './gsap.js'

const PREFERS_REDUCED_MOTION = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

const ANIMATIONS = {
    'fade-up':    { y: 40, opacity: 0 },
    'fade-down':  { y: -40, opacity: 0 },
    'fade-right': { x: -40, opacity: 0 },
    'fade-left':  { x: 40, opacity: 0 },
    'zoom-in':    { scale: 0.92, opacity: 0 },
    'zoom-out':   { scale: 1.08, opacity: 0 },
    'fade':       { opacity: 0 },
}

const DEFAULT_EASE     = 'power3.out'
const DEFAULT_DURATION = 0.9
const DEFAULT_START    = 'top 88%'

/**
 * Animar elementos con data-gsap="*"
 */
function animateElements() {
    const elements = document.querySelectorAll('[data-gsap]')
    if (!elements.length) return

    elements.forEach((el) => {
        const type     = el.dataset.gsap || 'fade-up'
        const delay    = parseFloat(el.dataset.gsapDelay || 0)
        const duration = parseFloat(el.dataset.gsapDuration || DEFAULT_DURATION)
        const ease     = el.dataset.gsapEase || DEFAULT_EASE
        const start    = el.dataset.gsapStart || DEFAULT_START
        const from     = ANIMATIONS[type] || ANIMATIONS['fade-up']

        gsap.from(el, {
            ...from,
            duration,
            delay,
            ease,
            immediateRender: true,
            scrollTrigger: {
                trigger: el,
                start,
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

        // Pre-fijar estado inicial para evitar flash hasta que el batch dispare.
        gsap.set(items, { opacity: 0, y: 40 })

        ScrollTrigger.batch(items, {
            onEnter: (elements) => {
                gsap.to(elements, {
                    opacity: 1,
                    y: 0,
                    duration: 0.75,
                    stagger: 0.09,
                    ease: DEFAULT_EASE,
                    overwrite: 'auto',
                })
            },
            start: 'top 88%',
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
 * Si el usuario prefiere movimiento reducido, hacemos visible cualquier elemento
 * marcado con data-gsap-* y salimos. El CSS también tiene un fallback paralelo
 * (ver src/main.css `@media (prefers-reduced-motion: reduce)`).
 */
function bypassForReducedMotion() {
    const els = document.querySelectorAll('[data-gsap], [data-gsap-batch] > *')
    els.forEach((el) => {
        el.style.opacity = ''
        el.style.transform = ''
    })
}

/**
 * Punto de entrada principal
 */
export function initScrollAnimations() {
    if (PREFERS_REDUCED_MOTION) {
        bypassForReducedMotion()
        return
    }

    animateElements()
    animateBatches()
    animateParallax()
    animateCounters()

    // Refrescar ScrollTrigger al redimensionar y cuando imágenes lazy carguen.
    ScrollTrigger.refresh()
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true })
}

export { gsap, ScrollTrigger }
