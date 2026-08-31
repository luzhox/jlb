/**
 * QA visual — RE-VALIDACIÓN del fix de la curva inferior del hero.
 *
 * frontend-lead quitó el domo `.jlb-hero::after` y añadió un
 * `<svg class="jlb-hero__wave">` con el mismo path que `.jlb-adm-hero__wave`.
 *
 * Mismas condiciones que el before: SIN admin bar (sesión pública),
 * animaciones desactivadas, scroll para disparar GSAP. Captura
 * element.screenshot() sobre `.jlb-hero` en 1440/834/390 → qa/live/
 * hero-curva-after-<vp>.png, y valida con computed style:
 *   - existe svg.jlb-hero__wave (height ~58px, width = ancho hero, bottom -1px)
 *   - YA NO existe `.jlb-hero::after` con border-radius elíptico
 *   - no-regresión: título/eyebrow/texto/botones/imagen presentes
 *   - la sección siguiente al hero no se rompió
 *   - sin errores de consola JS
 *
 * Solo diagnóstico/validación — NO edita código.
 *
 * Uso:  node qa/playwright-hero-curva-revalida.mjs
 */
import path from 'node:path';
import fs from 'node:fs';
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const LIVE_DIR = path.join(SCR_DIR, 'live');
fs.mkdirSync(LIVE_DIR, { recursive: true });

const VIEWPORTS = [
    { label: '1440', width: 1440, height: 900 },
    { label: '834',  width: 834,  height: 1112 },
    { label: '390',  width: 390,  height: 844 },
];
const KILL_ANIM = `*{animation:none!important;transition:none!important}`;

async function triggerScroll(page) {
    await page.evaluate(async () => {
        const total = document.body.scrollHeight;
        const step = window.innerHeight * 0.8;
        for (let y = 0; y <= total; y += step) {
            window.scrollTo(0, y);
            await new Promise(r => setTimeout(r, 80));
        }
        window.scrollTo(0, 0);
    });
    await page.waitForTimeout(600);
}

/** Métricas del wave nuevo + chequeo de que el domo ya no existe + no-regresión. */
async function inspect(page) {
    return page.evaluate(() => {
        const el = document.querySelector('.jlb-hero');
        if (!el) return { error: 'no .jlb-hero' };
        const er = el.getBoundingClientRect();

        // ::after (domo viejo): ¿sigue dibujándose con border-radius elíptico?
        const after = getComputedStyle(el, '::after');
        const afterStillDome =
            after.content !== 'none' &&
            after.content !== 'normal' &&
            /50%/.test(after.borderRadius) &&
            parseFloat(after.height) > 100; // domo era 150px

        // Wave nuevo.
        const wave = el.querySelector('.jlb-hero__wave');
        let waveInfo = null;
        if (wave) {
            const cs = getComputedStyle(wave);
            const wr = wave.getBoundingClientRect();
            const p = wave.querySelector('path');
            waveInfo = {
                tag: wave.tagName.toLowerCase(),
                position: cs.position,
                display: cs.display,
                height: cs.height,
                width: cs.width,
                bottom: cs.bottom,
                zIndex: cs.zIndex,
                pointerEvents: cs.pointerEvents,
                fill: p ? getComputedStyle(p).fill : null,
                renderedW: Math.round(wr.width),
                renderedH: Math.round(wr.height),
                pathD: p ? (p.getAttribute('d') || '').slice(0, 60) + '…' : null,
            };
        }

        // No-regresión: piezas de contenido.
        const content = {
            h1: !!el.querySelector('h1') && el.querySelector('h1').textContent.trim().slice(0, 40),
            eyebrow: !!el.querySelector('.jlb-hero__eyebrow'),
            paragraph: !!el.querySelector('p'),
            actions: el.querySelectorAll('.jlb-hero__actions a, .jlb-hero__actions button, .jlb-hero__actions .jlb-btn').length,
            mediaImg: !!el.querySelector('.jlb-hero__media img'),
        };

        // Sección que sigue al hero.
        const next = el.nextElementSibling;
        const nextRect = next ? next.getBoundingClientRect() : null;
        const nextInfo = next ? {
            tag: next.tagName.toLowerCase(),
            class: (next.className || '').toString().slice(0, 60),
            width: Math.round(nextRect.width),
            height: Math.round(nextRect.height),
            visible: nextRect.height > 0 && nextRect.width > 0,
        } : null;

        return {
            heroWidth: Math.round(er.width),
            heroHeight: Math.round(er.height),
            afterStillDome,
            afterContent: after.content,
            afterBorderRadius: after.borderRadius,
            afterHeight: after.height,
            waveExists: !!wave,
            waveInfo,
            content,
            next: nextInfo,
        };
    });
}

async function shotHero(page, file) {
    const loc = page.locator('.jlb-hero').first();
    await loc.scrollIntoViewIfNeeded();
    await page.waitForTimeout(300);
    await loc.screenshot({ path: file });
    console.log('📸', file);
}

const consoleErrors = [];
const results = {};

for (const vp of VIEWPORTS) {
    const { browser, page } = await launchBrowser({ viewport: { width: vp.width, height: vp.height } });
    page.on('console', m => { if (m.type() === 'error') consoleErrors.push(`[${vp.label}] ${m.text()}`); });
    page.on('pageerror', e => consoleErrors.push(`[${vp.label}] pageerror: ${e.message}`));

    try {
        console.log(`\n==> HOME @ ${vp.label}px`);
        // Cache-bust + bypass de cache para evitar el bundle CSS viejo.
        await page.route('**/*', route => {
            const h = { ...route.request().headers(), 'cache-control': 'no-cache', pragma: 'no-cache' };
            route.continue({ headers: h });
        });
        await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
        await page.addStyleTag({ content: KILL_ANIM });
        await page.waitForSelector('.jlb-hero', { timeout: 20000 });
        await triggerScroll(page);
        await page.addStyleTag({ content: KILL_ANIM });

        // ¿Qué bundle CSS cargó el navegador?
        const cssBundle = await page.evaluate(() =>
            [...document.querySelectorAll('link[rel="stylesheet"]')]
                .map(l => l.href).filter(h => /build\/css\/main-/.test(h))
        );
        console.log(`  CSS bundle @${vp.label}:`, cssBundle);

        results[vp.label] = await inspect(page);
        results[vp.label].cssBundle = cssBundle;
        console.log(`  inspect @${vp.label}:`, JSON.stringify(results[vp.label], null, 2));

        await shotHero(page, path.join(LIVE_DIR, `hero-curva-after-${vp.label}.png`));
    } catch (err) {
        console.error(`❌ @${vp.label}:`, err.message);
        try { await page.screenshot({ path: path.join(LIVE_DIR, `hero-curva-after-error-${vp.label}.png`), fullPage: true }); } catch {}
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

console.log('\n===== ERRORES DE CONSOLA =====');
console.log(consoleErrors.length ? consoleErrors.map(e => '  - ' + e).join('\n') : '  (ninguno)');

console.log('\n===== RESUMEN =====');
for (const vp of VIEWPORTS) {
    const r = results[vp.label];
    if (!r) { console.log(`@${vp.label}: SIN DATOS`); continue; }
    const w = r.waveInfo || {};
    console.log(`@${vp.label}: wave=${r.waveExists} h=${w.height} w=${w.width} bottom=${w.bottom} z=${w.zIndex} pe=${w.pointerEvents} | domoViejo=${r.afterStillDome} | next=${r.next ? r.next.class + ' visible:' + r.next.visible : 'NULL'}`);
}
