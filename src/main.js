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
import './menuMobile'
import './acordeon'
import './aosInit'      // AOS — compatibilidad con data-aos existentes

// ─── Preferencias globales ───────────────────────────────────────────────────
import './darkMode'        // Toggle dark mode global (localStorage + prefers-color-scheme)

// ─── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations()
})
