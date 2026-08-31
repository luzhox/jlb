/**
 * QA — sección video vertical del taller (layout 2-col copy + frame 9:16
 * y lightbox vertical). Uso: node qa/playwright-taller-video-vertical.mjs
 */
import { fileURLToPath } from 'node:url'
import { launchBrowser } from './lib/wp-playwright.mjs'

const PAGE_URL = 'http://jlb-school.local/talleres/taller-de-artes-plasticas/'
// fileURLToPath (no .pathname): la ruta del tema tiene espacios ("Local Sites")
// y .pathname deja el %20 → los PNG caerían en un dir "Local%20Sites" fantasma.
const OUT = fileURLToPath(new URL('./live/', import.meta.url))

const { browser } = await launchBrowser()

for (const [label, width, height] of [['1440', 1440, 960], ['390', 390, 844]]) {
    const page = await browser.newPage({ viewport: { width, height } })
    await page.goto(`${PAGE_URL}?qa_cb=${Date.now()}`, { waitUntil: 'domcontentloaded' })
    await page.waitForSelector('.jlb-taller-video', { timeout: 20000 })

    const section = page.locator('.jlb-taller-video')
    await section.scrollIntoViewIfNeeded()
    await page.waitForTimeout(800) // GSAP entrances

    const vertical = await section.evaluate((el) => el.classList.contains('jlb-taller-video--vertical'))
    const copy = await section.locator('.jlb-taller-video__copy').count()
    const ratio = await section.locator('.jlb-taller-video__frame').evaluate((el) => {
        const r = el.getBoundingClientRect()
        return (r.width / r.height).toFixed(2)
    })
    console.log(`[${label}] modifier vertical: ${vertical} · copy blocks: ${copy} · frame ratio: ${ratio} (esperado ~0.56)`)

    await section.screenshot({ path: `${OUT}taller-video-vertical-${label}.png` })

    if (label === '1440') {
        await section.locator('.jlb-taller-video__frame').click()
        await page.waitForTimeout(600)
        const lbVertical = await page.evaluate(() =>
            document.querySelector('.jlb-video-lightbox')?.classList.contains('is-vertical'))
        console.log(`[${label}] lightbox is-vertical: ${lbVertical}`)
        await page.screenshot({ path: `${OUT}taller-video-vertical-lightbox.png` })
    }
    await page.close()
}

await browser.close()
