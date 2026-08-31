/**
 * Diagnóstico: dump del DOM del .jlb-hero en el home — ¿se está renderizando
 * el markup nuevo del carousel (.jlb-hero__carousel.swiper) o el hero viejo?
 * Reporta clases, bundle CSS/JS cargado, y el outerHTML acotado del hero.
 * Solo diagnóstico — NO edita código.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
try {
    await page.route('**/*', route => {
        const h = { ...route.request().headers(), 'cache-control': 'no-cache', pragma: 'no-cache' };
        route.continue({ headers: h });
    });
    await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    await page.waitForSelector('.jlb-hero', { timeout: 20000 });

    const info = await page.evaluate(() => {
        const hero = document.querySelector('.jlb-hero');
        const bundles = {
            css: [...document.querySelectorAll('link[rel="stylesheet"]')].map(l => l.href).filter(h => /build\//.test(h)),
            js: [...document.querySelectorAll('script[src]')].map(s => s.src).filter(h => /build\//.test(h)),
        };
        const has = sel => !!document.querySelector(sel);
        return {
            bundles,
            heroExists: !!hero,
            heroClass: hero ? hero.className : null,
            heroOuterStart: hero ? hero.outerHTML.slice(0, 700) : null,
            checks: {
                carousel: has('.jlb-hero__carousel'),
                swiperWrapper: has('.jlb-hero .swiper-wrapper'),
                slides: document.querySelectorAll('.jlb-hero__slide').length,
                wave: has('.jlb-hero__wave'),
                dots: has('.jlb-hero__dots'),
                prev: has('.swiper-button-prev'),
                next: has('.swiper-button-next'),
                oldContentDirect: !!(hero && hero.querySelector(':scope > .jlb-hero__content')),
            },
        };
    });
    console.log(JSON.stringify(info, null, 2));
} catch (e) {
    console.error('❌', e.message);
    process.exitCode = 1;
} finally {
    await browser.close();
}
