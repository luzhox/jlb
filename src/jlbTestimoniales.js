/**
 * jlbTestimoniales.js — Slider del módulo jlb-testimoniales.
 *
 * Inicializa Swiper sobre cada .jlb-testimoniales__swiper.
 * Cada instancia usa sus propios botones `.jlb-testimoniales__prev/__next`
 * dentro del mismo .jlb-testimoniales (delimitados por proximidad para
 * permitir múltiples sliders en la misma página).
 *
 * Reglas:
 *   · Si solo hay 1 slide → oculta la navegación añadiendo `is-hidden`.
 *   · prefers-reduced-motion → speed muy bajo, fade instantáneo, sin loop.
 *   · loop solo cuando hay ≥ 2 slides.
 */
import Swiper from 'swiper'
import { A11y, EffectFade, Keyboard } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/effect-fade'

const PREFERS_REDUCED_MOTION = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

function initJlbTestimoniales() {
    const sliders = document.querySelectorAll('.jlb-testimoniales__swiper')
    if (!sliders.length) return

    sliders.forEach((el) => {
        const section = el.closest('.jlb-testimoniales') || document
        const slidesCount = el.querySelectorAll('.swiper-slide').length

        // La navegación (.jlb-testimoniales__nav) se renderiza DENTRO de cada
        // slide (forma parte del body). Con varios slides existen varios pares
        // de flechas; con el efecto fade solo el slide activo es visible, así
        // que el usuario siempre ve las flechas del slide activo. Por eso NO
        // usamos el módulo Navigation de Swiper (solo apuntaría a un par fijo y
        // se "congelaría" al cambiar de slide): enganchamos TODAS las flechas
        // manualmente a slidePrev()/slideNext().
        const navs = section.querySelectorAll('.jlb-testimoniales__nav')

        // 1 solo slide → ocultamos la nav (no hay a dónde navegar).
        if (slidesCount <= 1) {
            navs.forEach((nav) => nav.classList.add('is-hidden'))
        }

        const config = {
            modules: [A11y, Keyboard, EffectFade],
            slidesPerView: 1,
            spaceBetween: 0,
            loop: slidesCount > 1, // loop infinito: desde el último → primero y viceversa
            speed: PREFERS_REDUCED_MOTION ? 1 : 600,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: {
                prevSlideMessage: 'Testimonio anterior',
                nextSlideMessage: 'Testimonio siguiente',
            },
        }

        const swiper = new Swiper(el, config)

        if (slidesCount > 1) {
            section.querySelectorAll('.jlb-testimoniales__prev').forEach((btn) =>
                btn.addEventListener('click', () => swiper.slidePrev()))
            section.querySelectorAll('.jlb-testimoniales__next').forEach((btn) =>
                btn.addEventListener('click', () => swiper.slideNext()))
        }
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbTestimoniales)
} else {
    initJlbTestimoniales()
}

export { initJlbTestimoniales }
