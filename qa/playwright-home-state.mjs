/**
 * Diagnóstico: estado bruto del home. ¿Renderiza? ¿Error PHP? ¿Qué secciones hay?
 * Solo diagnóstico — NO edita código.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
const errs = [];
page.on('console', m => { if (m.type() === 'error' || m.type() === 'warning') errs.push(`[${m.type()}] ${m.text()}`); });
page.on('pageerror', e => errs.push(`[pageerror] ${e.message}`));
try {
    const resp = await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    console.log('HTTP status:', resp ? resp.status() : 'n/a');

    const info = await page.evaluate(() => {
        const sections = [...document.querySelectorAll('main section, .jlb-levels, [class*="jlb-"]')]
            .slice(0, 25).map(el => el.tagName.toLowerCase() + '.' + (el.className || '').toString().trim().replace(/\s+/g, '.').slice(0, 50));
        const bodyText = document.body ? document.body.innerText.slice(0, 400) : '(no body)';
        const phpError = /Fatal error|Parse error|Warning:|Notice:|There has been a critical error/i.test(document.body ? document.body.innerText : '');
        return {
            title: document.title,
            hasMain: !!document.querySelector('main'),
            bodyClass: document.body ? document.body.className : null,
            sectionsFound: sections,
            heroAny: document.querySelectorAll('[class*="jlb-hero"]').length,
            carouselAny: document.querySelectorAll('.jlb-hero__carousel, .swiper').length,
            phpErrorSuspected: phpError,
            bodyTextHead: bodyText,
        };
    });
    console.log(JSON.stringify(info, null, 2));
    console.log('\n=== CONSOLA ===');
    console.log(errs.length ? errs.map(e => '  - ' + e).join('\n') : '  (ninguno)');
} catch (e) {
    console.error('❌', e.message);
    process.exitCode = 1;
} finally {
    await browser.close();
}
