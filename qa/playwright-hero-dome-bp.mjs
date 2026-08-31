/**
 * Diagnóstico complementario: métricas del ::after domo de .jlb-hero
 * en tablet (834) y mobile (390), para documentar que el domo SIGUE
 * presente bajo 1180px (sin override en media queries) aunque el
 * element.screenshot no lo encuadre.
 *
 * Solo diagnóstico — NO edita código.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const VPS = [
    { label: '834', width: 834, height: 1112 },
    { label: '390', width: 390, height: 844 },
];
const KILL = `*{animation:none!important;transition:none!important}`;

for (const vp of VPS) {
    const { browser, page } = await launchBrowser({ viewport: { width: vp.width, height: vp.height } });
    try {
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });
        await page.addStyleTag({ content: KILL });
        await page.waitForSelector('.jlb-hero', { timeout: 20000 });

        const m = await page.evaluate(() => {
            const el = document.querySelector('.jlb-hero');
            const after = getComputedStyle(el, '::after');
            const media = document.querySelector('.jlb-hero__media');
            const er = el.getBoundingClientRect();
            const mr = media ? media.getBoundingClientRect() : null;
            return {
                afterHeight: after.height,
                afterBottom: after.bottom,
                afterBorderRadius: after.borderRadius,
                afterDisplay: after.display,
                gridCols: getComputedStyle(el).gridTemplateColumns,
                heroH: Math.round(er.height),
                mediaBottomRadius: media ? getComputedStyle(media).borderBottomLeftRadius : null,
                mediaTopRelToHero: mr ? Math.round(mr.top - er.top) : null,
            };
        });
        console.log(`@${vp.label}px =>`, JSON.stringify(m, null, 2));
    } catch (e) {
        console.error(`❌ @${vp.label}:`, e.message);
    } finally {
        await browser.close();
    }
}
