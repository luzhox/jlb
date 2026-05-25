/**
 * RE-VALIDACIÓN QA — fixes #1 (play arriba-derecha) y #2 (autor+flechas en fila).
 *
 * Recorre desktop 1440, tablet 834 y mobile 390. Anima off, admin bar off,
 * scroll previo. Guarda element.screenshot() de .jlb-testimoniales con sufijo
 * `-after` por breakpoint + crop del play. Mide criterios y emite veredicto.
 *
 * Uso:  node qa/playwright-testi-revalida.mjs
 */
import path from 'node:path';
import { launchBrowser, screenshot, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const BREAKPOINTS = [
    { name: '1440', width: 1440, height: 1200, primary: true },
    { name: '834', width: 834, height: 1400 },
    { name: '390', width: 390, height: 1600 },
];

const consoleErrors = [];
const results = {};

for (const bp of BREAKPOINTS) {
    const { browser, ctx, page } = await launchBrowser({ viewport: { width: bp.width, height: bp.height } });
    page.on('console', m => { if (m.type() === 'error') consoleErrors.push(`[${bp.name}] ${m.text()}`); });
    page.on('pageerror', e => consoleErrors.push(`[${bp.name}] pageerror: ${e.message}`));

    try {
        console.log(`\n===== Breakpoint ${bp.name} (${bp.width}px) =====`);
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });
        await page.addStyleTag({ content: '#wpadminbar{display:none!important} html{margin-top:0!important}' });
        await page.waitForSelector('.jlb-testimoniales', { timeout: 20000 });

        // Forzar visibilidad (GSAP reveal).
        await page.evaluate(() => {
            document.querySelectorAll('.jlb-testimoniales, .jlb-testimoniales *').forEach(el => {
                if (getComputedStyle(el).opacity === '0') el.style.opacity = '1';
                el.style.transform = el.style.transform || 'none';
            });
        });
        await page.locator('.jlb-testimoniales').scrollIntoViewIfNeeded();
        await page.waitForTimeout(1000);

        // Screenshot sección.
        const sec = page.locator('.jlb-testimoniales').first();
        const secFile = path.join(SCR_DIR, 'live', `testi-detalle-${bp.name}-after.png`);
        await sec.screenshot({ path: secFile });
        console.log('📸', secFile);

        // Crop del play.
        const playBox = await page.locator('.jlb-testimoniales__play').first().boundingBox();
        if (playBox) {
            const pFile = path.join(SCR_DIR, 'live', `testi-play-${bp.name}-after.png`);
            await page.screenshot({
                path: pFile,
                clip: { x: Math.max(0, playBox.x - 30), y: Math.max(0, playBox.y - 30), width: playBox.width + 60, height: playBox.height + 60 },
            });
            console.log('📸', pFile);
        }

        // Mediciones.
        const data = await page.evaluate(() => {
            const px = v => Math.round(v * 100) / 100;
            const r = el => el ? (b => ({ x: px(b.x), y: px(b.y), w: px(b.width), h: px(b.height) }))(el.getBoundingClientRect()) : null;
            const csv = (el, p) => el ? getComputedStyle(el)[p] : null;

            const media = document.querySelector('.jlb-testimoniales__media');
            const play = document.querySelector('.jlb-testimoniales__play');
            const author = document.querySelector('.jlb-testimoniales__author');
            const nav = document.querySelector('.jlb-testimoniales__nav');
            const actions = document.querySelector('.jlb-testimoniales__actions');
            const title = document.querySelector('.jlb-testimoniales__title');
            const quote = document.querySelector('.jlb-testimoniales__quote p');
            const card = document.querySelector('.jlb-testimoniales__card');
            const img = document.querySelector('.jlb-testimoniales__media img');

            const out = {};
            const mr = r(media), pr = r(play);
            out.media = mr;
            out.play = pr ? {
                rect: pr,
                width: csv(play, 'width'), height: csv(play, 'height'),
                borderRadius: csv(play, 'borderRadius'),
                position: csv(play, 'position'),
                topCss: csv(play, 'top'), rightCss: csv(play, 'right'), leftCss: csv(play, 'left'),
                transform: csv(play, 'transform'),
                isCircle: csv(play, 'borderRadius') === '50%' || /^(44|44\.|88px)/.test(csv(play, 'borderRadius')),
            } : null;
            if (mr && pr) {
                out.playOffsets = {
                    top: px(pr.y - mr.y),
                    right: px((mr.x + mr.w) - (pr.x + pr.w)),
                    left: px(pr.x - mr.x),
                    dxFromCenter: px((pr.x + pr.w / 2) - (mr.x + mr.w / 2)),
                    dyFromCenter: px((pr.y + pr.h / 2) - (mr.y + mr.h / 2)),
                    isTopRight: (pr.y - mr.y) < mr.h * 0.4 && ((mr.x + mr.w) - (pr.x + pr.w)) < mr.w * 0.4 && (pr.x - mr.x) > mr.w * 0.4,
                };
            }

            const ar = r(author), nr = r(nav);
            out.author = ar ? { rect: ar, textAlign: csv(author, 'textAlign'), marginLeft: csv(author, 'marginLeft') } : null;
            out.nav = nr ? { rect: nr } : null;
            out.actions = actions ? { rect: r(actions), display: csv(actions, 'display'), justifyContent: csv(actions, 'justifyContent'), flexDirection: csv(actions, 'flexDirection') } : null;
            if (ar && nr) {
                out.authorNav = {
                    authorCenterY: px(ar.y + ar.h / 2),
                    navCenterY: px(nr.y + nr.h / 2),
                    sameRow: Math.abs((ar.y + ar.h / 2) - (nr.y + nr.h / 2)) < 28,
                    navLeftOfAuthor: (nr.x + nr.w) <= ar.x + 2,
                    authorRightEdge: px(ar.x + ar.w),
                };
            }

            // Regresión: foto no recortada (img cubre media sin desbordar visiblemente).
            const ir = r(img);
            out.imgVsMedia = (ir && mr) ? {
                imgRect: ir, mediaRect: mr,
                covers: Math.abs(ir.w - mr.w) < 2 && Math.abs(ir.h - mr.h) < 2,
                objectFit: csv(img, 'objectFit'),
            } : null;

            out.title = title ? { fontSize: csv(title, 'fontSize'), fontFamily: csv(title, 'fontFamily'), color: csv(title, 'color') } : null;
            out.quote = quote ? { fontStyle: csv(quote, 'fontStyle'), fontSize: csv(quote, 'fontSize'), color: csv(quote, 'color') } : null;
            out.card = card ? { borderRadius: csv(card, 'borderRadius'), boxShadow: csv(card, 'boxShadow') } : null;
            return out;
        });

        results[bp.name] = data;
        console.log(JSON.stringify(data, null, 2));
    } catch (err) {
        console.error(`❌ [${bp.name}]`, err.message);
        try { await screenshot(page, `testi-revalida-error-${bp.name}`); } catch {}
        results[bp.name] = { error: err.message };
    } finally {
        await browser.close();
    }
}

// ── Veredicto sobre el primario (1440) ──────────────────────────────────────
console.log('\n\n========== VEREDICTO 1440 ==========');
const d = results['1440'] || {};
const c1 = d.playOffsets && d.playOffsets.isTopRight
    && Math.abs(d.playOffsets.top - 18) <= 4
    && Math.abs(d.playOffsets.right - 14) <= 4
    && d.play && d.play.rect && Math.abs(d.play.rect.w - 88) <= 2
    && d.play.borderRadius === '50%';
const c2 = d.authorNav && d.authorNav.sameRow && d.authorNav.navLeftOfAuthor
    && d.author && d.author.textAlign === 'right';
const c3 = d.imgVsMedia && d.imgVsMedia.covers && consoleErrors.length === 0;

console.log('#1 play arriba-derecha 88px círculo:', c1 ? '✓ APTO' : '✗ NO APTO');
console.log('#2 autor derecha en fila de flechas :', c2 ? '✓ APTO' : '✗ NO APTO');
console.log('#3 sin regresión (foto/JS)          :', c3 ? '✓ APTO' : '✗ NO APTO');
console.log('Errores de consola JS:', consoleErrors.length === 0 ? 'ninguno' : consoleErrors);

const ok = c1 && c2 && c3;
console.log(ok ? '\n✅ APTO (#1 y #2)' : '\n❌ NO APTO');
process.exitCode = ok ? 0 : 1;
