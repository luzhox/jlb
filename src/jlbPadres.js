/**
 * jlbPadres.js — Carrusel de "Lo que dicen los padres" (modules/jlb-testimonio-padres).
 *
 * Swiper con paginación (dots) + autoplay + loop. autoHeight para adaptar la
 * altura a citas de distinta longitud. Respeta prefers-reduced-motion.
 */
import Swiper from 'swiper'
import { Pagination, A11y, Autoplay } from 'swiper/modules'
import 'swiper/css'

const PREFERS_REDUCED_MOTION = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

function initJlbPadres() {
    const sliders = document.querySelectorAll('.jlb-testimonial__swiper')
    if (!sliders.length) return

    sliders.forEach((el) => {
        const section = el.closest('.jlb-testimonial') || document
        const count   = el.querySelectorAll('.swiper-slide').length
        const dots    = section.querySelector('.jlb-testimonial__dots')

        if (count <= 1) return // sin carrusel con un solo testimonio

        // eslint-disable-next-line no-new
        new Swiper(el, {
            modules: [Pagination, A11y, Autoplay],
            slidesPerView: 1,
            spaceBetween: 24,
            loop: true,
            autoHeight: true,
            speed: PREFERS_REDUCED_MOTION ? 0 : 550,
            autoplay: PREFERS_REDUCED_MOTION ? false : { delay: 6500, disableOnInteraction: false },
            a11y: { enabled: true },
            pagination: dots
                ? {
                    el: dots,
                    clickable: true,
                    bulletClass: 'jlb-testimonial__dot',
                    bulletActiveClass: 'is-active',
                }
                : false,
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJlbPadres)
} else {
    initJlbPadres()
}

export { initJlbPadres }
