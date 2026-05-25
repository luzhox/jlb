// Captura el cierre del drawer a varios timings para validar suavidad real.
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const URL = process.env.QA_URL || 'http://jlb-school.local/'
const exe = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const browser = await chromium.launch({ executablePath: exe, headless: true })
const ctx = await browser.newContext({
    viewport: { width: 390, height: 900 },
    deviceScaleFactor: 2,
})
const page = await ctx.newPage()
await page.goto(URL, { waitUntil: 'networkidle' })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(500)

// Captura el OPEN a varios frames (60ms / 150ms / 280ms / 500ms post-click).
await page.click('[data-jlb-menu-toggle]')

for (const [name, ms] of [['open-060', 60], ['open-150', 150], ['open-280', 280], ['open-500', 500]]) {
    await page.waitForTimeout(ms - (name === 'open-060' ? 0 : parseInt(name.split('-')[1]) - (name === 'open-150' ? 60 : name === 'open-280' ? 150 : 280)))
}

// Mejor: capturar absoluto con timestamps controlados
await page.goto(URL, { waitUntil: 'networkidle' })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(500)

// OPEN
await page.click('[data-jlb-menu-toggle]')
const opens = [80, 200, 380]
for (let i = 0; i < opens.length; i++) {
    const delta = i === 0 ? opens[0] : opens[i] - opens[i - 1]
    await page.waitForTimeout(delta)
    await page.screenshot({ path: path.join(__dirname, `trans-open-${opens[i]}ms.png`), clip: { x: 0, y: 0, width: 390, height: 900 } })
}
// Wait full transition
await page.waitForTimeout(200)

// CLOSE
await page.click('.jlb-mobile-nav__close')
const closes = [60, 180, 320]
for (let i = 0; i < closes.length; i++) {
    const delta = i === 0 ? closes[0] : closes[i] - closes[i - 1]
    await page.waitForTimeout(delta)
    await page.screenshot({ path: path.join(__dirname, `trans-close-${closes[i]}ms.png`), clip: { x: 0, y: 0, width: 390, height: 900 } })
}

console.log('✓ Transiciones capturadas')
await browser.close()
