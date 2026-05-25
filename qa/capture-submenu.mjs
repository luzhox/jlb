// QA del menú mobile actualizado: drawer cerrado / abierto / submenú expandido,
// más una captura "a mitad de animación" para verificar la suavidad del cierre.
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
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
})
const page = await ctx.newPage()
await page.goto(URL, { waitUntil: 'networkidle' })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(500)

// 1. Drawer cerrado (estado base)
await page.screenshot({ path: path.join(__dirname, 'sub-01-closed.png'), clip: { x: 0, y: 0, width: 390, height: 120 } })

// 2. Abrir drawer (esperar al final de la transición — 460ms panel + 80ms delay del cascade)
await page.click('[data-jlb-menu-toggle]')
await page.waitForTimeout(700)
await page.screenshot({ path: path.join(__dirname, 'sub-02-open.png'), clip: { x: 0, y: 0, width: 390, height: 900 } })

// 3. Toggle submenú de Niveles (segundo item, index 1)
await page.click('[data-jlb-submenu-toggle]')
await page.waitForTimeout(550)
await page.screenshot({ path: path.join(__dirname, 'sub-03-submenu-open.png'), clip: { x: 0, y: 0, width: 390, height: 900 } })

// 4. Mid-close: dispara cierre y captura a la mitad de la animación
//    (para validar que el cierre TAMBIÉN se anima, no es un cut)
await page.click('.jlb-mobile-nav__close')
await page.waitForTimeout(200) // ~mitad del 460ms del panel
await page.screenshot({ path: path.join(__dirname, 'sub-04-mid-close.png'), clip: { x: 0, y: 0, width: 390, height: 900 } })

// 5. Cierre completo
await page.waitForTimeout(400)
await page.screenshot({ path: path.join(__dirname, 'sub-05-closed-final.png'), clip: { x: 0, y: 0, width: 390, height: 200 } })

console.log('✓ Capturas drawer + submenu listas')
await browser.close()
