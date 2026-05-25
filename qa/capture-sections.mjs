// QA por sección — captura cada módulo del front-page por separado para
// poder yuxtaponer con el Figma de referencia con precisión.
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const URL = process.env.QA_URL || 'http://jlb-school.local/'

const executablePath = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const sections = [
    { name: 'hero',         selector: '.jlb-hero' },
    { name: 'header',       selector: '.jlb-header' },
    { name: 'niveles',      selector: '.jlb-levels' },
    { name: 'manifesto',    selector: '.jlb-manifesto' },
    { name: 'experience',   selector: '.jlb-experience' },
    { name: 'testimonio',   selector: '.jlb-testimonial' },
    { name: 'noticias',     selector: '.jlb-news' },
    { name: 'footer',       selector: '.jlb-footer' },
]

const browser = await chromium.launch({ executablePath, headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 })
const page = await ctx.newPage()
await page.goto(URL, { waitUntil: 'networkidle' })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(800)
// Scroll completo para forzar lazy-loaded images
await page.evaluate(async () => {
    await new Promise(resolve => {
        let y = 0
        const step = () => {
            window.scrollTo(0, y); y += 800
            if (y >= document.body.scrollHeight + 800) {
                window.scrollTo(0, 0); setTimeout(resolve, 300)
            } else setTimeout(step, 80)
        }
        step()
    })
})
await page.waitForTimeout(500)

for (const s of sections) {
    const el = await page.$(s.selector)
    if (!el) {
        console.log(`✗ ${s.name} — selector "${s.selector}" no encontrado`)
        continue
    }
    const box = await el.boundingBox()
    const out = path.join(__dirname, `section-${s.name}.png`)
    await el.screenshot({ path: out })
    console.log(`✓ ${s.name.padEnd(11)} ${Math.round(box.width)}×${Math.round(box.height)} → ${out}`)
}
await browser.close()
console.log('Done.')
