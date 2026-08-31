/**
 * QA visual — Carrusel Swiper del .jlb-hero (home).
 *
 * NO desactiva las animaciones de Swiper (necesitamos ver el slideo);
 * solo pausa autoplay para los screenshots estáticos. SIN admin bar,
 * cache-bust + no-cache para evitar el bundle viejo.
 *
 * Valida:
 *  1. Swiper inicializó (.swiper-initialized), 2 slides reales, next/dot cambia
 *     slide, .jlb-hero__slide display:grid (no block), columna imagen height>0.
 *  2. Controles @1440: dots + 2 flechas visibles, por encima del wave, bullet
 *     activo blanco.
 *  3. Wave intacto: z-index:2, width=ancho hero, bottom -1px.
 *  4. Responsive 834/390: 1 columna, flechas display:none, dots visibles, wave
 *     full-width.
 *  5. No-regresión: 1er slide <h1>, 2º <h2>; .jlb-levels OK; sin errores consola.
 *
 * Solo diagnóstico — NO edita código.   Uso: node qa/playwright-hero-carousel.mjs
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

const consoleMsgs = []; // errores + warnings (Swiper warnings importan)

async function gotoHome(page) {
    await page.route('**/*', route => {
        const h = { ...route.request().headers(), 'cache-control': 'no-cache', pragma: 'no-cache' };
        route.continue({ headers: h });
    });
    await page.goto(`${WP_SITE_URL}/?qa_cb=${Date.now()}`, { waitUntil: 'networkidle' });
    await page.waitForSelector('.jlb-hero__carousel', { timeout: 20000 });
    // Espera a que Swiper inicialice.
    await page.waitForFunction(() => {
        const c = document.querySelector('.jlb-hero__carousel');
        return c && c.classList.contains('swiper-initialized');
    }, { timeout: 10000 }).catch(() => {});
    await page.waitForTimeout(800);
}

/** Pausa autoplay y vuelve al primer slide para screenshots deterministas. */
async function pauseAutoplay(page) {
    await page.evaluate(() => {
        const c = document.querySelector('.jlb-hero__carousel');
        const sw = c && c.swiper;
        if (sw) {
            if (sw.autoplay && sw.autoplay.stop) sw.autoplay.stop();
            if (sw.slideToLoop) sw.slideToLoop(0, 0); else if (sw.slideTo) sw.slideTo(0, 0);
        }
    });
    await page.waitForTimeout(400);
}

async function shotHero(page, file) {
    const loc = page.locator('.jlb-hero').first();
    await loc.scrollIntoViewIfNeeded();
    await page.waitForTimeout(300);
    await loc.screenshot({ path: file });
    console.log('📸', file);
}

async function inspect(page) {
    return page.evaluate(() => {
        const sec = document.querySelector('.jlb-hero');
        const car = document.querySelector('.jlb-hero__carousel');
        const sw = car && car.swiper;

        // Slides reales (no clones de loop).
        const realSlides = [...document.querySelectorAll('.jlb-hero__slide:not(.swiper-slide-duplicate)')];
        const firstSlide = realSlides[0] || null;
        const slideCS = firstSlide ? getComputedStyle(firstSlide) : null;
        const mediaEl = firstSlide ? firstSlide.querySelector('.jlb-hero__media') : null;
        const mediaRect = mediaEl ? mediaEl.getBoundingClientRect() : null;

        // Wave.
        const wave = car ? car.querySelector('.jlb-hero__wave') : null;
        const waveCS = wave ? getComputedStyle(wave) : null;
        const waveRect = wave ? wave.getBoundingClientRect() : null;

        // Controles.
        const prev = document.querySelector('.swiper-button-prev');
        const next = document.querySelector('.swiper-button-next');
        const dots = document.querySelector('.jlb-hero__dots');
        const bullets = [...document.querySelectorAll('.jlb-hero__dots .swiper-pagination-bullet')];
        const activeBullet = document.querySelector('.jlb-hero__dots .swiper-pagination-bullet-active');
        const vis = el => { if (!el) return null; const c = getComputedStyle(el); const r = el.getBoundingClientRect(); return { display: c.display, visibility: c.visibility, zIndex: c.zIndex, w: Math.round(r.width), h: Math.round(r.height) }; };

        // Headings.
        const h1s = sec ? sec.querySelectorAll('.jlb-hero__slide:not(.swiper-slide-duplicate) h1').length : 0;
        const h2s = sec ? sec.querySelectorAll('.jlb-hero__slide:not(.swiper-slide-duplicate) h2').length : 0;

        const nextSection = sec ? sec.nextElementSibling : null;
        const nextRect = nextSection ? nextSection.getBoundingClientRect() : null;

        return {
            initialized: !!(car && car.classList.contains('swiper-initialized')),
            swiperPresent: !!sw,
            loop: sw ? !!sw.params.loop : null,
            realSlideCount: realSlides.length,
            domSlideCount: document.querySelectorAll('.jlb-hero__slide').length,
            activeIndex: sw ? (sw.realIndex ?? sw.activeIndex) : null,
            slideDisplay: slideCS ? slideCS.display : null,
            slideGridCols: slideCS ? slideCS.gridTemplateColumns : null,
            mediaHeight: mediaRect ? Math.round(mediaRect.height) : null,
            wave: waveCS ? { position: waveCS.position, zIndex: waveCS.zIndex, width: waveCS.width, height: waveCS.height, bottom: waveCS.bottom, renderedW: Math.round(waveRect.width) } : null,
            controls: { prev: vis(prev), next: vis(next), dots: vis(dots), bulletCount: bullets.length, activeBulletBg: activeBullet ? getComputedStyle(activeBullet).backgroundColor : null },
            headings: { h1: h1s, h2: h2s },
            nextSection: nextSection ? { class: (nextSection.className || '').toString().slice(0, 50), h: nextRect ? Math.round(nextRect.height) : 0, visible: nextRect ? nextRect.height > 0 : false } : null,
        };
    });
}

const results = {};

for (const vp of VIEWPORTS) {
    const { browser, page } = await launchBrowser({ viewport: { width: vp.width, height: vp.height } });
    page.on('console', m => { if (m.type() === 'error' || m.type() === 'warning') consoleMsgs.push(`[${vp.label}/${m.type()}] ${m.text()}`); });
    page.on('pageerror', e => consoleMsgs.push(`[${vp.label}/pageerror] ${e.message}`));

    try {
        console.log(`\n==> HOME @ ${vp.label}px`);
        await gotoHome(page);

        // Inspección inicial (con autoplay aún activo para leer estado real).
        const before = await inspect(page);

        // Probar navegación: índice antes, click next, índice después.
        const idxBefore = await page.evaluate(() => { const s = document.querySelector('.jlb-hero__carousel').swiper; return s ? (s.realIndex ?? s.activeIndex) : null; });
        await pauseAutoplay(page);
        let navWorks = null, idxAfter = null;
        try {
            await page.locator('.swiper-button-next').click({ timeout: 3000 });
            await page.waitForTimeout(800);
            idxAfter = await page.evaluate(() => { const s = document.querySelector('.jlb-hero__carousel').swiper; return s ? (s.realIndex ?? s.activeIndex) : null; });
            navWorks = idxAfter !== idxBefore;
        } catch (e) {
            // En responsive las flechas pueden estar display:none → click falla; usar dot.
            try {
                const dots = page.locator('.jlb-hero__dots .swiper-pagination-bullet');
                if (await dots.count() > 1) {
                    await dots.nth(1).click({ force: true, timeout: 3000 });
                    await page.waitForTimeout(800);
                    idxAfter = await page.evaluate(() => { const s = document.querySelector('.jlb-hero__carousel').swiper; return s ? (s.realIndex ?? s.activeIndex) : null; });
                    navWorks = idxAfter !== idxBefore;
                }
            } catch {}
        }

        // Screenshot del slide 2 activo (solo @1440).
        if (vp.label === '1440' && navWorks) {
            await page.waitForTimeout(400);
            await shotHero(page, path.join(LIVE_DIR, 'hero-carousel-slide2-1440.png'));
        }

        // Volver al slide 1, autoplay pausado, screenshot estático.
        await pauseAutoplay(page);
        await shotHero(page, path.join(LIVE_DIR, `hero-carousel-${vp.label}.png`));

        results[vp.label] = { ...before, idxBefore, idxAfter, navWorks };
        console.log(`  inspect @${vp.label}:`, JSON.stringify(results[vp.label], null, 2));
    } catch (err) {
        console.error(`❌ @${vp.label}:`, err.message);
        try { await page.screenshot({ path: path.join(LIVE_DIR, `hero-carousel-error-${vp.label}.png`), fullPage: true }); } catch {}
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

console.log('\n===== CONSOLA (errores + warnings) =====');
console.log(consoleMsgs.length ? consoleMsgs.map(m => '  - ' + m).join('\n') : '  (ninguno)');

console.log('\n===== RESUMEN =====');
for (const vp of VIEWPORTS) {
    const r = results[vp.label]; if (!r) { console.log(`@${vp.label}: SIN DATOS`); continue; }
    console.log(`@${vp.label}: init=${r.initialized} slidesReales=${r.realSlideCount} loop=${r.loop} nav=${r.navWorks}(${r.idxBefore}->${r.idxAfter}) slideDisplay=${r.slideDisplay} mediaH=${r.mediaHeight} | wave z=${r.wave?.zIndex} w=${r.wave?.renderedW} bottom=${r.wave?.bottom} | prev=${r.controls.prev?.display} next=${r.controls.next?.display} dots=${r.controls.dots?.display} bullets=${r.controls.bulletCount} activeBg=${r.controls.activeBulletBg} | h1=${r.headings.h1} h2=${r.headings.h2} | next=${r.nextSection?.class} vis=${r.nextSection?.visible}`);
}
