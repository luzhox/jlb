/**
 * main.js — Entrada Vite
 * Stack: Vite + Tailwind CSS v4 + GSAP + Swiper
 */

// ─── Estilos ──────────────────────────────────────────────────────────────────
import './main.css'                          // Tailwind CSS v4 + tokens
import '../styles/sass/style.scss'           // Estilos SASS existentes (migración progresiva)

// ─── Animaciones GSAP ─────────────────────────────────────────────────────────
import { initScrollAnimations } from './animations/onScroll.js'

// ─── Módulos ──────────────────────────────────────────────────────────────────
import './scrollHeader'
import './intersectionObserver'
import './inputFields'
import './modals'
import './carousel'     // Swiper
import './jlbTestimoniales' // Slider de testimoniales JLB (modules/jlb-testimoniales)
import './jlbVideoLightbox'  // Lightbox de video (data-jlb-video) — experience + testimoniales
import './jlbImageLightbox'  // Lightbox de imagen / zoom (data-jlb-zoom) — galería Admisión
import './jlbPadres'          // Carrusel "Lo que dicen los padres" (modules/jlb-testimonio-padres)
import './jlbManifesto'        // Gradiente progresivo de las palabras bold (modules/jlb-manifesto)
import './jlbProceso'          // Stepper/tabs (modules/jlb-proceso) — página Admisión
import './jlbCuota'            // Calculadora de cuota (modules/jlb-cuota) — página Admisión
import './jlbFaq'              // Acordeón FAQ (modules/jlb-faq) — página Admisión
import './jlbOpenDayForm'      // Formulario Open Day → HubSpot (modules/jlb-open-day-form)
import './menuMobile'
import './jlbMobileNav'  // Drawer mobile específico del template JLB (header-jlb.php).
import './acordeon'
import './aosInit'      // AOS — compatibilidad con data-aos existentes

// ─── Preferencias globales ───────────────────────────────────────────────────
import './darkMode'        // Toggle dark mode global (localStorage + prefers-color-scheme)

// ─── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations()
})
