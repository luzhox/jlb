/**
 * QA visual — diagnóstico de la curva inferior del hero.
 *
 * Target: .jlb-hero (home) usa un ::after domo elíptico blanco.
 * Referencia: .jlb-adm-hero (Admisión) usa un <svg.jlb-adm-hero__wave> suave.
 *
 * Captura SIN admin bar (sesión deslogueada = público), animaciones
 * desactivadas (animation/transition:none), scroll para disparar GSAP,
 * en 3 viewports (1440 objetivo, 834 tablet, 390 mobile). Hace
 * element.screenshot() sobre cada hero y dumpea métricas del ::after
 * (jlb-hero) y del wave (jlb-adm-hero) para evidenciar la discrepancia.
 *
 * Solo diagnóstico — NO edita código.
 *
 * Uso:  node qa/playwright-hero-curva-diag.mjs
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

/** Scroll lento hasta el fondo y vuelta arriba para disparar GSAP/ScrollTrigger. */
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

/** Métricas del domo ::after de .jlb-hero. */
async function measureHeroDome(page) {
    return page.evaluate(() => {
        const el = document.querySelector('.jlb-hero');
        if (!el) return { error: 'no .jlb-hero' };
        const after = getComputedStyle(el, '::after');
        const host = getComputedStyle(el);
        const rect = el.getBoundingClientRect();
        return {
            selector: '.jlb-hero::after',
            content: after.content,
            position: after.position,
            display: after.display,
            background: after.backgroundColor,
            borderRadius: after.borderRadius,
            height: after.height,
            width: after.width,
            bottom: after.bottom,
            left: after.left,
            right: after.right,
            hostOverflow: host.overflow,
            heroWidth: Math.round(rect.width),
            heroHeight: Math.round(rect.height),
        };
    });
}

/** Métricas del wave svg de .jlb-adm-hero. */
async function measureAdmWave(page) {
    return page.evaluate(() => {
        const host = document.querySelector('.jlb-adm-hero');
        if (!host) return { error: 'no .jlb-adm-hero' };
        const wave = host.querySelector('.jlb-adm-hero__wave');
        const rect = host.getBoundingClientRect();
        if (!wave) {
            const after = getComputedStyle(host, '::after');
            return { warning: 'no .jlb-adm-hero__wave element', afterContent: after.content };
        }
        const cs = getComputedStyle(wave);
        const wr = wave.getBoundingClientRect();
        const path = wave.querySelector('path');
        return {
            selector: '.jlb-adm-hero__wave',
            tag: wave.tagName.toLowerCase(),
            position: cs.position,
            display: cs.display,
            height: cs.height,
            width: cs.width,
            bottom: cs.bottom,
            fill: path ? getComputedStyle(path).fill : null,
            renderedW: Math.round(wr.width),
            renderedH: Math.round(wr.height),
            pathD: path ? (path.getAttribute('d') || '').slice(0, 80) + '…' : null,
            heroWidth: Math.round(rect.width),
            heroHeight: Math.round(rect.height),
        };
    });
}

/** element.screenshot sobre un selector. */
async function shotElement(page, selector, file) {
    const loc = page.locator(selector).first();
    await loc.scrollIntoViewIfNeeded();
    await page.waitForTimeout(300);
    await loc.screenshot({ path: file });
    console.log('📸', file);
}

const consoleErrors = [];

(async () => {
    const results = { home: {}, admision: {} };

    for (const vp of VIEWPORTS) {
        const { browser, ctx, page } = await launchBrowser({
            viewport: { width: vp.width, height: vp.height },
        });

        // Captura errores de consola del home específicamente.
        page.on('console', m => {
            if (m.type() === 'error') consoleErrors.push(`[${vp.label}] ${m.text()}`);
        });
        page.on('pageerror', e => consoleErrors.push(`[${vp.label}] pageerror: ${e.message}`));

        try {
            // ====== HOME — .jlb-hero ======
            console.log(`\n==> HOME @ ${vp.label}px`);
            await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });
            await page.addStyleTag({ content: KILL_ANIM });
            await page.waitForSelector('.jlb-hero', { timeout: 20000 });
            await triggerScroll(page);
            await page.addStyleTag({ content: KILL_ANIM }); // re-aplica tras GSAP inline

            if (vp.label === '1440') {
                results.home.dome = await measureHeroDome(page);
                console.log('  jlb-hero ::after =>', JSON.stringify(results.home.dome, null, 2));
            }
            await shotElement(page, '.jlb-hero',
                path.join(LIVE_DIR, `hero-curva-before-${vp.label}.png`));

            // ====== ADMISIÓN — .jlb-adm-hero ======
            console.log(`==> ADMISIÓN @ ${vp.label}px`);
            await page.goto(`${WP_SITE_URL}/admision/`, { waitUntil: 'networkidle' });
            await page.addStyleTag({ content: KILL_ANIM });
            await page.waitForSelector('.jlb-adm-hero', { timeout: 20000 });
            await triggerScroll(page);
            await page.addStyleTag({ content: KILL_ANIM });

            if (vp.label === '1440') {
                results.admision.wave = await measureAdmWave(page);
                console.log('  jlb-adm-hero wave =>', JSON.stringify(results.admision.wave, null, 2));
            }
            await shotElement(page, '.jlb-adm-hero',
                path.join(LIVE_DIR, `admhero-curva-ref-${vp.label}.png`));

        } catch (err) {
            console.error(`❌ @${vp.label}:`, err.message);
            try {
                await page.screenshot({ path: path.join(LIVE_DIR, `hero-curva-error-${vp.label}.png`), fullPage: true });
            } catch {}
            process.exitCode = 1;
        } finally {
            await browser.close();
        }
    }

    console.log('\n===== ERRORES DE CONSOLA (home + admision) =====');
    if (consoleErrors.length === 0) console.log('  (ninguno)');
    else consoleErrors.forEach(e => console.log('  -', e));

    console.log('\n===== RESUMEN MÉTRICAS @1440 =====');
    console.log('jlb-hero (domo ::after):', JSON.stringify(results.home.dome));
    console.log('jlb-adm-hero (wave):    ', JSON.stringify(results.admision.wave));
})();
