// QA de animaciones de entrada. Para cada sección:
//   1. Reload limpio (sin animaciones previas).
//   2. Scroll hasta DENTRO de la sección (centro del viewport).
//   3. Espera el cascade (1.4s) y captura.
// El reload garantiza que cada sección se vea con su animación de entrada,
// no en estado "ya jugado".
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const URL = process.env.QA_URL || 'http://jlb-school.local/'
const exe = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const sections = [
    { name: 'hero',       selector: '.jlb-hero' },
    { name: 'niveles',    selector: '.jlb-levels' },
    { name: 'manifesto',  selector: '.jlb-manifesto' },
    { name: 'experience', selector: '.jlb-experience' },
    { name: 'testimonio', selector: '.jlb-testimonial' },
    { name: 'noticias',   selector: '.jlb-news' },
]

const browser = await chromium.launch({ executablePath: exe, headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

for (const s of sections) {
    await page.goto(URL, { waitUntil: 'networkidle' })
    await page.evaluate(() => document.fonts && document.fonts.ready)
    await page.waitForTimeout(300)

    if (s.name === 'hero') {
        // Hero es above-the-fold, no scroll necesario
        await page.waitForTimeout(1400)
        await page.screenshot({ path: path.join(__dirname, `anim-${s.name}-final.png`), clip: { x: 0, y: 0, width: 1440, height: 800 } })
    } else {
        // Posicionar la sección con su top a ~10% del viewport para verla completa entrar
        const targetY = await page.evaluate(sel => {
            const el = document.querySelector(sel)
            if (!el) return 0
            return Math.max(0, el.getBoundingClientRect().top + window.scrollY - 80)
        }, s.selector)

        // Step 1: scroll a 200px ANTES del trigger, captura "before"
        await page.evaluate(y => window.scrollTo({ top: y, behavior: 'instant' }), Math.max(0, targetY - 300))
        await page.waitForTimeout(80)
        await page.screenshot({ path: path.join(__dirname, `anim-${s.name}-before.png`), clip: { x: 0, y: 0, width: 1440, height: 900 } })

        // Step 2: scroll a la posición de visión y esperar el cascade
        await page.evaluate(y => window.scrollTo({ top: y, behavior: 'instant' }), targetY)
        await page.waitForTimeout(1500)
        await page.screenshot({ path: path.join(__dirname, `anim-${s.name}-after.png`), clip: { x: 0, y: 0, width: 1440, height: 900 } })
    }

    console.log(`✓ ${s.name}`)
}

await browser.close()
console.log('Done.')
