/**
 * Diagnóstico de entorno: ¿el home está sirviendo CSS de Vite DEV (HMR) o el
 * fallback de build de producción? Determina por qué el fix SCSS del wave
 * (presente en _jlb-hero.scss) NO se refleja en el navegador.
 *
 * - Lista los <link rel=stylesheet> y <script type=module> del home.
 * - Detecta si hay @vite/client (señal de dev) o build/.../main.css (prod).
 * - Comprueba si localhost:5173 responde.
 * - Busca la regla CSS .jlb-hero__wave en las hojas cargadas y reporta si
 *   el ::after de .jlb-hero todavía tiene reglas (domo).
 *
 * Solo diagnóstico — NO edita código.
 */
import { launchBrowser, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
try {
    await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });

    const assets = await page.evaluate(() => {
        const links = [...document.querySelectorAll('link[rel="stylesheet"]')].map(l => l.href);
        const scripts = [...document.querySelectorAll('script[type="module"], script[src]')].map(s => s.src).filter(Boolean);
        const viteClient = scripts.some(s => /@vite\/client|:5173/.test(s)) || links.some(s => /:5173/.test(s));
        return { links, scripts, viteClient };
    });
    console.log('=== STYLESHEETS ===');
    assets.links.forEach(l => console.log('  link:', l));
    console.log('=== SCRIPTS (module/src) ===');
    assets.scripts.forEach(s => console.log('  script:', s));
    console.log('=== ¿Vite DEV? ===', assets.viteClient);

    // ¿Responde el dev server?
    let devUp = false;
    try {
        const r = await page.request.get('http://localhost:5173/@vite/client', { timeout: 3000 });
        devUp = r.ok();
    } catch { devUp = false; }
    console.log('=== localhost:5173/@vite/client responde? ===', devUp);

    // Buscar reglas CSS aplicables.
    const cssProbe = await page.evaluate(() => {
        let waveRuleFound = false, afterRuleFound = false, waveRuleText = null, afterRuleText = null;
        for (const sheet of document.styleSheets) {
            let rules;
            try { rules = sheet.cssRules; } catch { continue; } // CORS
            if (!rules) continue;
            for (const rule of rules) {
                const sel = rule.selectorText || '';
                if (sel.includes('.jlb-hero__wave')) { waveRuleFound = true; waveRuleText = rule.cssText.slice(0, 160); }
                if (sel.includes('.jlb-hero') && sel.includes('::after')) { afterRuleFound = true; afterRuleText = rule.cssText.slice(0, 160); }
            }
        }
        return { waveRuleFound, waveRuleText, afterRuleFound, afterRuleText };
    });
    console.log('=== CSS RULES ===');
    console.log('  .jlb-hero__wave regla encontrada:', cssProbe.waveRuleFound, '|', cssProbe.waveRuleText);
    console.log('  .jlb-hero::after regla encontrada:', cssProbe.afterRuleFound, '|', cssProbe.afterRuleText);

} catch (e) {
    console.error('❌', e.message);
    process.exitCode = 1;
} finally {
    await browser.close();
}
