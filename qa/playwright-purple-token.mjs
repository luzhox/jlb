/**
 * Diagnóstico: ¿está definida --jlb-purple en runtime? ¿Qué valor resuelve en
 * el botón de la flecha y en :root? Por qué el chevron sale blanco en vez de
 * morado. Solo diagnóstico — NO edita.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
try {
    await page.route('**/*', route => { route.continue({ headers: { ...route.request().headers(), 'cache-control': 'no-cache' } }); });
    await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    await page.waitForSelector('.jlb-hero__carousel.swiper-initialized', { timeout: 15000 });
    await page.waitForTimeout(500);

    const info = await page.evaluate(() => {
        const root = document.documentElement;
        const btn = document.querySelector('.jlb-hero .swiper-button-prev');
        const hero = document.querySelector('.jlb-hero');
        const readVar = (el, name) => getComputedStyle(el).getPropertyValue(name).trim() || '(vacío/no definida)';
        return {
            purpleOnRoot: readVar(root, '--jlb-purple'),
            purpleOnHero: hero ? readVar(hero, '--jlb-purple') : null,
            purpleOnBtn: btn ? readVar(btn, '--jlb-purple') : null,
            btnComputedColor: btn ? getComputedStyle(btn).color : null,
            heroContentColor: (() => { const c = document.querySelector('.jlb-hero__content'); return c ? getComputedStyle(c).color : null; })(),
            // tokens hermanos por si ayudan a ubicar dónde se define la paleta
            inkOnRoot: readVar(root, '--jlb-ink'),
            gradientOnRoot: readVar(root, '--jlb-gradient'),
            // buscar reglas que declaren --jlb-purple
            purpleDeclaredIn: (() => {
                const hits = [];
                for (const sheet of document.styleSheets) {
                    let rules; try { rules = sheet.cssRules; } catch { continue; }
                    if (!rules) continue;
                    for (const r of rules) {
                        if (r.cssText && r.cssText.includes('--jlb-purple')) hits.push(r.selectorText || r.cssText.slice(0, 40));
                    }
                }
                return hits.slice(0, 10);
            })(),
        };
    });
    console.log(JSON.stringify(info, null, 2));
} catch (e) {
    console.error('❌', e.message); process.exitCode = 1;
} finally { await browser.close(); }
