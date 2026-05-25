// QA específico del menú mobile: estado cerrado vs abierto.
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const URL = process.env.QA_URL || 'http://jlb-school.local/'
const exe = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const browser = await chromium.launch({ executablePath: exe, headless: true })

for (const w of [390, 768]) {
    const ctx = await browser.newContext({
        viewport: { width: w, height: 900 },
        deviceScaleFactor: 2,
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    })
    const page = await ctx.newPage()
    await page.goto(URL, { waitUntil: 'networkidle' })
    await page.evaluate(() => document.fonts && document.fonts.ready)
    await page.waitForTimeout(500)

    // Estado CERRADO — viewport del header
    await page.screenshot({ path: path.join(__dirname, `mobile-menu-closed-${w}.png`), clip: { x: 0, y: 0, width: w, height: 120 } })

    // Click en hamburger
    await page.click('[data-jlb-menu-toggle]')
    await page.waitForTimeout(500) // esperar entrada de cascade

    // Estado ABIERTO — viewport completo
    await page.screenshot({ path: path.join(__dirname, `mobile-menu-open-${w}.png`), clip: { x: 0, y: 0, width: w, height: 900 } })

    console.log(`✓ ${w}px capturado`)
    await ctx.close()
}

await browser.close()
console.log('Done.')
