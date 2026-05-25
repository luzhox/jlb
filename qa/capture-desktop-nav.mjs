// Captura el nav desktop con submenu hover sobre "Niveles".
import { chromium } from 'playwright-core'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const exe = '/Users/luismorales/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'

const browser = await chromium.launch({ executablePath: exe, headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()
await page.goto('http://jlb-school.local/', { waitUntil: 'networkidle' })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(500)

// Capturar el nav sin hover
await page.screenshot({ path: path.join(__dirname, 'desktop-nav-idle.png'), clip: { x: 0, y: 0, width: 1440, height: 110 } })

// Hover sobre el item "Niveles" para mostrar dropdown
await page.hover('.jlb-header__nav-item--has-children > a')
await page.waitForTimeout(400)
await page.screenshot({ path: path.join(__dirname, 'desktop-nav-hover.png'), clip: { x: 0, y: 0, width: 1440, height: 280 } })

console.log('✓ Desktop nav capturado')
await browser.close()
