/**
 * QA visual pixel-perfect del Home (front-page) contra Figma.
 *
 * - Captura el home en vivo SIN admin bar (sesión deslogueada → público).
 * - Desktop 1440px: full-page + por sección (element.screenshot()).
 * - Tablet 834px y Mobile 390px: solo full-page (verificar que no se rompa).
 * - Desactiva animaciones (GSAP/CSS) para capturar el estado final.
 * - Mide computed styles clave en el DOM y dumpea errores de consola.
 *
 * Uso:   node qa/playwright-home-pixelperfect.mjs
 */
import path from 'node:path';
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const LIVE_DIR = path.join(SCR_DIR, 'live');
const KILL_ANIM = '*{animation:none!important;transition:none!important;animation-duration:0s!important;scroll-behavior:auto!important}';

const SECTIONS = [
    ['header', '.jlb-header'],
    ['hero', '.jlb-hero'],
    ['niveles', '.jlb-levels'],
    ['manifesto', '.jlb-manifesto'],
    ['experience', '.jlb-experience'],
    ['testimoniales', '.jlb-testimoniales'],
    ['testimonio-padres', '.jlb-testimonial'],
    ['noticias', '.jlb-news'],
    ['footer', '.jlb-footer'],
];

const consoleErrors = [];

async function settle(page) {
    await page.addStyleTag({ content: KILL_ANIM });
    // Forzar carga de todas las imágenes lazy haciendo scroll completo.
    await page.evaluate(async () => {
        await new Promise(res => {
            let y = 0;
            const step = () => {
                window.scrollTo(0, y);
                y += window.innerHeight;
                if (y < document.body.scrollHeight) setTimeout(step, 60);
                else { window.scrollTo(0, 0); setTimeout(res, 300); }
            };
            step();
        });
    });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(800);
}

async function run() {
    // Viewport desktop 1440, scale 1, sin login → sin admin bar.
    const { browser, ctx, page } = await launchBrowser({
        viewport: { width: 1440, height: 1200 },
        headless: true,
    });

    page.on('console', m => {
        if (m.type() === 'error') consoleErrors.push(m.text());
    });
    page.on('pageerror', e => consoleErrors.push('[pageerror] ' + e.message));

    try {
        // ── DESKTOP ──────────────────────────────────────────────────────────
        console.log('==> DESKTOP 1440');
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'domcontentloaded' });
        await settle(page);

        // Confirmar que NO hay admin bar.
        const hasAdminBar = await page.locator('#wpadminbar').count();
        console.log('  #wpadminbar presente:', hasAdminBar ? 'SÍ (ojo)' : 'no ✓');

        await page.screenshot({ path: path.join(LIVE_DIR, 'home-desktop-full.png'), fullPage: true });
        console.log('  📸 home-desktop-full.png');

        // Por sección.
        const measures = {};
        for (const [name, sel] of SECTIONS) {
            const loc = page.locator(sel).first();
            const count = await loc.count();
            if (!count) { console.log(`  ⚠️ sección "${name}" (${sel}) NO encontrada`); continue; }
            await loc.scrollIntoViewIfNeeded().catch(() => {});
            await page.waitForTimeout(150);
            await loc.screenshot({ path: path.join(LIVE_DIR, `${name}.png`) }).catch(e => console.log(`  ⚠️ screenshot ${name}: ${e.message}`));
            // Medición de geometría + estilos clave.
            measures[name] = await loc.evaluate(el => {
                const r = el.getBoundingClientRect();
                const cs = getComputedStyle(el);
                return {
                    w: Math.round(r.width), h: Math.round(r.height),
                    bg: cs.backgroundColor,
                    pt: cs.paddingTop, pb: cs.paddingBottom,
                };
            });
            console.log(`  📸 ${name}.png`, JSON.stringify(measures[name]));
        }

        // Mediciones tipográficas/contenedor específicas.
        const detail = await page.evaluate(() => {
            const grab = (sel, props) => {
                const el = document.querySelector(sel);
                if (!el) return { _missing: sel };
                const cs = getComputedStyle(el);
                const r = el.getBoundingClientRect();
                const out = { w: Math.round(r.width) };
                for (const p of props) out[p] = cs[p];
                return out;
            };
            return {
                container: grab('.jlb-container', ['maxWidth', 'paddingLeft', 'paddingRight', 'marginLeft']),
                heroTitle: grab('.jlb-hero__title, .jlb-hero h1', ['fontSize', 'fontFamily', 'fontWeight', 'lineHeight', 'color']),
                levelsTitle: grab('.jlb-levels__title, .jlb-levels h2', ['fontSize', 'fontFamily', 'fontWeight', 'lineHeight', 'color']),
                manifesto: grab('.jlb-manifesto p, .jlb-manifesto__text', ['fontSize', 'fontFamily', 'fontWeight', 'lineHeight', 'color']),
                btnLight: grab('.jlb-btn--light, .jlb-btn', ['backgroundColor', 'borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomLeftRadius', 'borderBottomRightRadius', 'color', 'fontSize']),
                footer: grab('.jlb-footer', ['backgroundColor', 'background', 'color']),
                footerBottom: grab('.jlb-footer__bottom, .jlb-footer__legal', ['backgroundColor', 'color']),
                body: grab('body', ['fontFamily', 'backgroundColor']),
            };
        });
        console.log('\n==> MEDICIONES DETALLE');
        console.log(JSON.stringify(detail, null, 2));

        // ── TABLET ───────────────────────────────────────────────────────────
        console.log('\n==> TABLET 834');
        await page.setViewportSize({ width: 834, height: 1112 });
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'domcontentloaded' });
        await settle(page);
        await page.screenshot({ path: path.join(LIVE_DIR, 'home-tablet-full.png'), fullPage: true });
        console.log('  📸 home-tablet-full.png');
        const tabletOverflow = await page.evaluate(() => ({
            scrollW: document.documentElement.scrollWidth,
            clientW: document.documentElement.clientWidth,
            overflowX: document.documentElement.scrollWidth > document.documentElement.clientWidth + 2,
        }));
        console.log('  overflow tablet:', JSON.stringify(tabletOverflow));

        // ── MOBILE ───────────────────────────────────────────────────────────
        console.log('\n==> MOBILE 390');
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'domcontentloaded' });
        await settle(page);
        await page.screenshot({ path: path.join(LIVE_DIR, 'home-mobile-full.png'), fullPage: true });
        console.log('  📸 home-mobile-full.png');
        const mobileOverflow = await page.evaluate(() => ({
            scrollW: document.documentElement.scrollWidth,
            clientW: document.documentElement.clientWidth,
            overflowX: document.documentElement.scrollWidth > document.documentElement.clientWidth + 2,
        }));
        console.log('  overflow mobile:', JSON.stringify(mobileOverflow));

        // ── ERRORES DE CONSOLA ────────────────────────────────────────────────
        console.log('\n==> ERRORES DE CONSOLA JS:', consoleErrors.length);
        for (const e of consoleErrors) console.log('  •', e);

        console.log('\n✅ Capturas completas');
    } catch (err) {
        console.error('❌', err.message);
        try { await page.screenshot({ path: path.join(LIVE_DIR, 'home-error.png'), fullPage: true }); } catch {}
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

run();
