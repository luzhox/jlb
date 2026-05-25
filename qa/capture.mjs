// QA visual: capturas full-page del front-page JLB en desktop y mobile.
// Usa el chromium ya descargado por Playwright (cache global).
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const URL = process.env.QA_URL || 'http://jlb-school.local/'

const executablePath = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const targets = [
    { name: 'local-desktop', viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 },
    { name: 'local-mobile',  viewport: { width: 390,  height: 844 }, deviceScaleFactor: 2 },
]

const browser = await chromium.launch({ executablePath, headless: true })
for (const t of targets) {
    const ctx = await browser.newContext({
        viewport: t.viewport,
        deviceScaleFactor: t.deviceScaleFactor,
        userAgent: t.name.includes('mobile')
            ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
            : undefined,
    })
    const page = await ctx.newPage()
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 30000 })
    // Esperar fuentes (Google Fonts) y un breve settle para imágenes lazy iniciales.
    await page.evaluate(() => document.fonts && document.fonts.ready)
    await page.waitForTimeout(800)
    // Forzar carga de imágenes lazy: scroll completo y vuelta arriba.
    await page.evaluate(async () => {
        await new Promise(resolve => {
            let y = 0
            const step = () => {
                window.scrollTo(0, y)
                y += 800
                if (y >= document.body.scrollHeight + 800) {
                    window.scrollTo(0, 0)
                    setTimeout(resolve, 300)
                } else setTimeout(step, 100)
            }
            step()
        })
    })
    await page.waitForTimeout(500)
    const out = path.join(__dirname, `${t.name}.png`)
    await page.screenshot({ path: out, fullPage: true })
    console.log(`✓ ${t.name} → ${out} (${t.viewport.width}×${t.viewport.height})`)
    await ctx.close()
}
await browser.close()
console.log('Done.')
