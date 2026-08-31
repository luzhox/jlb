/**
 * QA visual — Flechas mejoradas del carrusel .jlb-hero (home).
 *
 * Cambio: círculo BLANCO SÓLIDO + sombra + chevron SVG propio morado
 * (var(--jlb-purple) #614794 = rgb(97,71,148)), caps redondeados.
 *
 * SIN admin bar, cache-bust + no-cache. NO desactiva animaciones de Swiper
 * (pausa autoplay para estáticos). Valida en runtime:
 *  1. ESTILO @1440: prev/next bg blanco sólido, 48x48, redondo, box-shadow;
 *     chevron <svg.jlb-hero__chev> 22x22 stroke=currentColor, botón color morado.
 *  2. GLIFO VIEJO: ::after de swiper-button sin glifo (content none/"").
 *  3. CONTRASTE: chevron morado sobre blanco (valores de color).
 *  4. NO-REGRESIÓN: nav cambia slide, dots blancos, wave intacto, flechas
 *     ocultas @834. Sin errores de consola.
 *
 * Capturas: hero-arrows-after-1440.png + hero-arrows-after-detail.png (crop).
 * Solo diagnóstico — NO edita.   Uso: node qa/playwright-hero-arrows.mjs
 */
import path from 'node:path';
import fs from 'node:fs';
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const LIVE_DIR = path.join(SCR_DIR, 'live');
fs.mkdirSync(LIVE_DIR, { recursive: true });

const consoleMsgs = [];

async function gotoHome(page) {
    await page.route('**/*', route => {
        const h = { ...route.request().headers(), 'cache-control': 'no-cache', pragma: 'no-cache' };
        route.continue({ headers: h });
    });
    await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    await page.waitForSelector('.jlb-hero__carousel.swiper-initialized', { timeout: 15000 });
    await page.waitForTimeout(700);
}

async function pauseAutoplay(page) {
    await page.evaluate(() => {
        const sw = document.querySelector('.jlb-hero__carousel')?.swiper;
        if (sw?.autoplay?.stop) sw.autoplay.stop();
        if (sw?.slideToLoop) sw.slideToLoop(0, 0);
    });
    await page.waitForTimeout(300);
}

/** Métricas completas de una flecha (prev o next). */
function probeArrow(sel) {
    const btn = document.querySelector(sel);
    if (!btn) return { error: 'no btn ' + sel };
    const cs = getComputedStyle(btn);
    const r = btn.getBoundingClientRect();
    const after = getComputedStyle(btn, '::after');
    const before = getComputedStyle(btn, '::before');
    const chev = btn.querySelector('.jlb-hero__chev');
    const chevCS = chev ? getComputedStyle(chev) : null;
    const chevR = chev ? chev.getBoundingClientRect() : null;
    const chevPath = chev ? chev.querySelector('path') : null;
    return {
        display: cs.display,
        background: cs.backgroundColor,
        color: cs.color,
        w: Math.round(r.width),
        h: Math.round(r.height),
        borderRadius: cs.borderRadius,
        boxShadow: cs.boxShadow,
        zIndex: cs.zIndex,
        afterContent: after.content,
        beforeContent: before.content,
        chev: chev ? {
            tag: chev.tagName.toLowerCase(),
            w: Math.round(chevR.width),
            h: Math.round(chevR.height),
            stroke: chevPath ? getComputedStyle(chevPath).stroke : chevCS.stroke,
            strokeLinecap: chevPath ? getComputedStyle(chevPath).strokeLinecap : null,
            color: chevCS.color,
        } : null,
    };
}

(async () => {
    // ===== @1440 — estilo, glifo viejo, contraste, captura =====
    {
        const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } });
        page.on('console', m => { if (m.type() === 'error' || m.type() === 'warning') consoleMsgs.push(`[1440/${m.type()}] ${m.text()}`); });
        page.on('pageerror', e => consoleMsgs.push(`[1440/pageerror] ${e.message}`));
        try {
            console.log('\n==> HOME @ 1440px');
            await gotoHome(page);
            await pauseAutoplay(page);

            const arrows = await page.evaluate((fn) => {
                const probe = new Function('sel', `return (${fn})(sel);`);
                return { prev: probe('.swiper-button-prev'), next: probe('.swiper-button-next') };
            }, probeArrow.toString());
            console.log('  prev:', JSON.stringify(arrows.prev, null, 2));
            console.log('  next:', JSON.stringify(arrows.next, null, 2));

            // Nav: next cambia slide.
            const idxBefore = await page.evaluate(() => document.querySelector('.jlb-hero__carousel').swiper.realIndex);
            await page.locator('.swiper-button-next').click({ timeout: 3000 });
            await page.waitForTimeout(700);
            const idxAfter = await page.evaluate(() => document.querySelector('.jlb-hero__carousel').swiper.realIndex);
            console.log(`  nav: ${idxBefore} -> ${idxAfter} (cambia=${idxAfter !== idxBefore})`);

            // Dots + wave (no-regresión).
            const extra = await page.evaluate(() => {
                const activeBullet = document.querySelector('.jlb-hero__dots .swiper-pagination-bullet-active');
                const wave = document.querySelector('.jlb-hero__wave');
                const wCS = wave ? getComputedStyle(wave) : null;
                const wR = wave ? wave.getBoundingClientRect() : null;
                return {
                    activeBulletBg: activeBullet ? getComputedStyle(activeBullet).backgroundColor : null,
                    wave: wCS ? { zIndex: wCS.zIndex, bottom: wCS.bottom, w: Math.round(wR.width) } : null,
                };
            });
            console.log('  dots/wave:', JSON.stringify(extra));

            // Volver a slide 1 y capturar.
            await pauseAutoplay(page);
            const hero = page.locator('.jlb-hero').first();
            await hero.scrollIntoViewIfNeeded();
            await page.waitForTimeout(300);
            await hero.screenshot({ path: path.join(LIVE_DIR, 'hero-arrows-fixed-1440.png') });
            console.log('📸', path.join(LIVE_DIR, 'hero-arrows-fixed-1440.png'));

            // Crop a la flecha prev (con margen).
            const pbox = await page.locator('.swiper-button-prev').boundingBox();
            if (pbox) {
                const pad = 24;
                await page.screenshot({
                    path: path.join(LIVE_DIR, 'hero-arrows-fixed-detail.png'),
                    clip: { x: Math.max(0, pbox.x - pad), y: Math.max(0, pbox.y - pad), width: pbox.width + pad * 2, height: pbox.height + pad * 2 },
                });
                console.log('📸', path.join(LIVE_DIR, 'hero-arrows-fixed-detail.png'));
            }

            globalThis.__r1440 = { arrows, idxBefore, idxAfter, extra };
        } catch (e) {
            console.error('❌ @1440:', e.message);
            try { await page.screenshot({ path: path.join(LIVE_DIR, 'hero-arrows-error-1440.png'), fullPage: true }); } catch {}
            process.exitCode = 1;
        } finally { await browser.close(); }
    }

    // ===== @834 — flechas ocultas =====
    let r834 = null;
    {
        const { browser, page } = await launchBrowser({ viewport: { width: 834, height: 1112 } });
        page.on('console', m => { if (m.type() === 'error' || m.type() === 'warning') consoleMsgs.push(`[834/${m.type()}] ${m.text()}`); });
        page.on('pageerror', e => consoleMsgs.push(`[834/pageerror] ${e.message}`));
        try {
            console.log('\n==> HOME @ 834px');
            await gotoHome(page);
            r834 = await page.evaluate(() => {
                const prev = document.querySelector('.swiper-button-prev');
                const next = document.querySelector('.swiper-button-next');
                const dots = document.querySelector('.jlb-hero__dots');
                return {
                    prevDisplay: prev ? getComputedStyle(prev).display : null,
                    nextDisplay: next ? getComputedStyle(next).display : null,
                    dotsDisplay: dots ? getComputedStyle(dots).display : null,
                };
            });
            console.log('  @834:', JSON.stringify(r834));
        } catch (e) {
            console.error('❌ @834:', e.message); process.exitCode = 1;
        } finally { await browser.close(); }
    }

    console.log('\n===== CONSOLA (errores + warnings) =====');
    console.log(consoleMsgs.length ? consoleMsgs.map(m => '  - ' + m).join('\n') : '  (ninguno)');
})();
