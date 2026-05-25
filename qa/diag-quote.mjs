import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs'

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } })
await page.goto(`${WP_SITE_URL}/qa-testimoniales-%c2%b7-multi/`, { waitUntil: 'networkidle' })
await page.waitForSelector('.jlb-testimoniales', { timeout: 15000 })
await page.evaluate(() => document.fonts && document.fonts.ready)
await page.waitForTimeout(800)

const data = await page.evaluate(() => {
    const quote = document.querySelector('.jlb-testimoniales__quote')
    if (!quote) return { found: false }
    const dump = (el) => {
        if (!el) return null
        const cs = getComputedStyle(el)
        return {
            tag: el.tagName,
            classes: el.className,
            text: el.textContent.trim().slice(0, 80),
            color: cs.color,
            fontFamily: cs.fontFamily,
            fontStyle: cs.fontStyle,
            fontWeight: cs.fontWeight,
            fontSize: cs.fontSize,
        }
    }
    return {
        found: true,
        outerHTML: quote.outerHTML.slice(0, 800),
        self: dump(quote),
        children: Array.from(quote.children).map(dump),
        descendants: Array.from(quote.querySelectorAll('*')).map(dump),
    }
})

console.log(JSON.stringify(data, null, 2))
await browser.close()
