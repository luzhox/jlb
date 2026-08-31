/**
 * Reconocimiento del DOM del formulario de admisión (.jlb-admform) en
 * /admision/. Dumpea estructura: campos, names, tipos, opciones del select,
 * checkbox, botón, status aria-live, y el endpoint del form. Para escribir el
 * test E2E con selectores reales. Solo diagnóstico — NO edita.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
const errs = [];
page.on('console', m => { if (m.type() === 'error' || m.type() === 'warning') errs.push(`[${m.type()}] ${m.text()}`); });
page.on('pageerror', e => errs.push(`[pageerror] ${e.message}`));
try {
    await page.route('**/*', route => route.continue({ headers: { ...route.request().headers(), 'cache-control': 'no-cache' } }));
    await page.goto(`${WP_SITE_URL}/admision/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    const exists = await page.locator('.jlb-admform').count();
    console.log('.jlb-admform count:', exists);
    if (!exists) {
        const secs = await page.evaluate(() => [...document.querySelectorAll('[class*="jlb-"]')].map(e => e.className.toString().split(' ')[0]).filter((v,i,a)=>a.indexOf(v)===i).slice(0,40));
        console.log('clases jlb presentes:', secs);
    } else {
        await page.locator('.jlb-admform').scrollIntoViewIfNeeded();
        const info = await page.evaluate(() => {
            const sec = document.querySelector('.jlb-admform');
            const form = sec.querySelector('form');
            const fields = [...sec.querySelectorAll('input, select, textarea')].map(el => ({
                tag: el.tagName.toLowerCase(),
                type: el.type || null,
                name: el.name || null,
                id: el.id || null,
                required: el.required,
                options: el.tagName === 'SELECT' ? [...el.options].map(o => o.value + ':' + o.textContent.trim()) : undefined,
            }));
            const btn = sec.querySelector('button[type="submit"], button:not([type])');
            const status = sec.querySelector('[aria-live], [role="status"], .jlb-admform__status');
            return {
                formAction: form ? form.getAttribute('action') : null,
                formMethod: form ? form.method : null,
                dataEndpoint: form ? (form.dataset.endpoint || form.getAttribute('data-endpoint')) : null,
                fields,
                submitText: btn ? btn.textContent.trim() : null,
                submitClass: btn ? btn.className : null,
                statusSelector: status ? (status.className || status.getAttribute('role') || status.getAttribute('aria-live')) : '(no encontrado)',
                statusAriaLive: status ? status.getAttribute('aria-live') : null,
                secOuterStart: sec.outerHTML.slice(0, 400),
            };
        });
        console.log(JSON.stringify(info, null, 2));
    }
    console.log('\n=== CONSOLA ===');
    console.log(errs.length ? errs.map(e => '  - ' + e).join('\n') : '  (ninguno)');
} catch (e) {
    console.error('❌', e.message); process.exitCode = 1;
} finally { await browser.close(); }
