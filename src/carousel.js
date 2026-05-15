/**
 * carousel.js — Swiper (reemplaza Owl Carousel)
 *
 * Inicializa cada carrusel solo si su elemento existe en el DOM.
 * Todos los contenedores deben tener la estructura:
 *
 *   <div class="swiper [nombre-carrusel]">
 *     <div class="swiper-wrapper">
 *       <div class="swiper-slide">…</div>
 *     </div>
 *     <div class="swiper-button-prev"></div>
 *     <div class="swiper-button-next"></div>
 *     <div class="swiper-pagination"></div>  <!-- opcional -->
 *   </div>
 */
import Swiper from 'swiper'
import { Navigation, Pagination, Autoplay, HashNavigation } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

/**
 * Crea un Swiper si el selector encuentra un elemento en el DOM.
 * @param {string|Element} selector
 * @param {object} config
 * @returns {Swiper|null}
 */
function createSwiper(selector, config) {
    const el = typeof selector === 'string'
        ? document.querySelector(selector)
        : selector
    if (!el) return null

    return new Swiper(el, {
        modules: [Navigation, Pagination, Autoplay, HashNavigation],
        ...config,
    })
}

// ── Hero principal ─────────────────────────────────────────────────────────
createSwiper('.hero-container', {
    loop: true,
    speed: 800,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
    },
    navigation: {
        nextEl: '.hero-container .swiper-button-next',
        prevEl: '.hero-container .swiper-button-prev',
    },
})

// ── Hero de servicio con hash URL ──────────────────────────────────────────
createSwiper('.hero-service__hero', {
    speed: 600,
    hashNavigation: { watchState: true },
    navigation: {
        nextEl: '.hero-service__hero .swiper-button-next',
        prevEl: '.hero-service__hero .swiper-button-prev',
    },
})

// ── Clientes ───────────────────────────────────────────────────────────────
createSwiper('.clients__items', {
    loop: true,
    speed: 600,
    slidesPerView: 1,
    spaceBetween: 16,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: '.clients__items .swiper-button-next',
        prevEl: '.clients__items .swiper-button-prev',
    },
    breakpoints: {
        600: { slidesPerView: 4, spaceBetween: 32 },
    },
})

// ── Equipo ─────────────────────────────────────────────────────────────────
createSwiper('.team__carousel', {
    loop: true,
    speed: 600,
    slidesPerView: 2,
    spaceBetween: 0,
    navigation: {
        nextEl: '.team__carousel .swiper-button-next',
        prevEl: '.team__carousel .swiper-button-prev',
    },
    breakpoints: {
        600: { slidesPerView: 5 },
    },
})

// ── Historias de éxito ─────────────────────────────────────────────────────
createSwiper('.success-stories__content', {
    speed: 600,
    slidesPerView: 1,
    spaceBetween: 32,
    navigation: {
        nextEl: '.success-stories__content .swiper-button-next',
        prevEl: '.success-stories__content .swiper-button-prev',
    },
    breakpoints: {
        600: { slidesPerView: 3, slidesPerGroup: 1 },
    },
})

// ── Testimonios ────────────────────────────────────────────────────────────
createSwiper('.testimonios__slider', {
    loop: true,
    speed: 600,
    slidesPerView: 1,
    spaceBetween: 24,
    autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
    navigation: {
        nextEl: '.testimonios__slider .swiper-button-next',
        prevEl: '.testimonios__slider .swiper-button-prev',
    },
    pagination: { el: '.testimonios__slider .swiper-pagination', clickable: true },
    breakpoints: {
        744:  { slidesPerView: 2 },
        1240: { slidesPerView: 3 },
    },
})

// ── Carrusel de texto en header ────────────────────────────────────────────
createSwiper('.header-principal__text__carousel', {
    loop: true,
    speed: 600,
    autoplay: {
        delay: 4000,
        disableOnInteraction: false,
    },
})

// ── Botones de hero de servicio (solo desktop) ─────────────────────────────
if (window.matchMedia('(min-width: 900px)').matches) {
    createSwiper('.hero-service__buttons', {
        speed: 600,
        slidesPerView: 3,
        spaceBetween: 20,
        navigation: {
            nextEl: '.hero-service__buttons .swiper-button-next',
            prevEl: '.hero-service__buttons .swiper-button-prev',
        },
        breakpoints: {
            900: { slidesPerView: 5 },
        },
    })
}
