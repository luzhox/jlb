/**
 * GSAP setup — importar desde aquí para garantizar que
 * ScrollTrigger siempre esté registrado antes de usarlo.
 */
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

export { gsap, ScrollTrigger }
