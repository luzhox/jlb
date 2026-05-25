/**
 * QA visual DETALLE — slider de testimoniales en DESKTOP 1440 vs Figma.
 *
 * Navega al home público (no admin), oculta admin bar, desactiva animaciones
 * (prefers-reduced-motion + GSAP reveal), hace scroll a la sección, toma
 * element.screenshot() de .jlb-testimoniales y un crop, y dumpea computados
 * de los sub-elementos (play, autor, arco, comilla, tipos, card, overlay).
 *
 * Uso:  node qa/playwright-testi-detalle.mjs
 */
import path from 'node:path';
import { launchBrowser, screenshot, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, ctx, page } = await launchBrowser({ viewport: { width: 1440, height: 1200 } });

try {
    console.log('==> 1 Navegar al home (1440), animaciones off');
    // Forzar reduced-motion para que GSAP no deje opacity:0.
    await ctx.addInitScript(() => {
        try {
            const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        } catch {}
    });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });

    // Ocultar admin bar si está logueado (no lo estamos, pero por si acaso).
    await page.addStyleTag({ content: '#wpadminbar{display:none!important} html{margin-top:0!important}' });

    await page.waitForSelector('.jlb-testimoniales', { timeout: 20000 });

    // Forzar visibilidad de elementos animados por GSAP (data-gsap deja opacity:0
    // hasta el reveal; con reduced-motion debería estar visible, pero forzamos).
    await page.evaluate(() => {
        document.querySelectorAll('.jlb-testimoniales [data-gsap], .jlb-testimoniales [style*="opacity"]').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
            el.style.visibility = 'visible';
        });
        // gsap set inline en el contenedor:
        document.querySelectorAll('.jlb-testimoniales, .jlb-testimoniales *').forEach(el => {
            if (getComputedStyle(el).opacity === '0') el.style.opacity = '1';
        });
    });

    await page.locator('.jlb-testimoniales').scrollIntoViewIfNeeded();
    await page.waitForTimeout(1200);

    console.log('==> 2 Screenshots del vivo');
    const sec = page.locator('.jlb-testimoniales').first();
    await sec.screenshot({ path: path.join(SCR_DIR, 'live', 'testi-detalle-1440.png') });
    console.log('📸', path.join(SCR_DIR, 'live', 'testi-detalle-1440.png'));

    // Crop del play.
    const playBox = await page.locator('.jlb-testimoniales__play').first().boundingBox();
    const cardBox = await page.locator('.jlb-testimoniales__card').first().boundingBox();
    if (playBox) {
        await page.screenshot({
            path: path.join(SCR_DIR, 'live', 'testi-play-live.png'),
            clip: { x: Math.max(0, playBox.x - 30), y: Math.max(0, playBox.y - 30), width: playBox.width + 60, height: playBox.height + 60 },
        });
    }
    // Crop del arco decor.
    const decorBox = await page.locator('.jlb-testimoniales__decor').first().boundingBox();
    if (decorBox) {
        await page.screenshot({
            path: path.join(SCR_DIR, 'live', 'testi-arco-live.png'),
            clip: { x: Math.max(0, decorBox.x - 10), y: Math.max(0, decorBox.y - 10), width: decorBox.width + 20, height: decorBox.height + 20 },
        });
    }

    console.log('\n==> 3 Asserts / computados del vivo');

    const data = await page.evaluate(() => {
        const px = v => Math.round(v * 100) / 100;
        const out = {};

        const card = document.querySelector('.jlb-testimoniales__card');
        const slide = document.querySelector('.jlb-testimoniales__slide');
        const media = document.querySelector('.jlb-testimoniales__media');
        const play = document.querySelector('.jlb-testimoniales__play');
        const overlay = document.querySelector('.jlb-testimoniales__media-overlay');
        const kicker = document.querySelector('.jlb-testimoniales__kicker');
        const title = document.querySelector('.jlb-testimoniales__title');
        const quote = document.querySelector('.jlb-testimoniales__quote p');
        const qmark = document.querySelector('.jlb-testimoniales__quote-mark');
        const author = document.querySelector('.jlb-testimoniales__author');
        const nav = document.querySelector('.jlb-testimoniales__nav');
        const body = document.querySelector('.jlb-testimoniales__body');
        const decor = document.querySelector('.jlb-testimoniales__decor');

        const rect = el => el ? (b => ({ x: px(b.x), y: px(b.y), w: px(b.width), h: px(b.height) }))(el.getBoundingClientRect()) : null;
        const cs = (el, props) => {
            if (!el) return null;
            const s = getComputedStyle(el);
            const o = {};
            props.forEach(p => o[p] = s[p]);
            return o;
        };

        if (card) {
            out.card = {
                rect: rect(card),
                ...cs(card, ['borderRadius', 'boxShadow', 'backgroundColor']),
            };
        }
        if (slide) out.slide = cs(slide, ['display', 'gridTemplateColumns', 'minHeight']);
        if (media) {
            out.media = {
                rect: rect(media),
                ...cs(media, ['borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomRightRadius', 'borderBottomLeftRadius', 'margin', 'minHeight', 'maxHeight']),
            };
        }
        if (play) {
            const pr = rect(play);
            const cr = rect(card);
            const mr = rect(media);
            out.play = {
                rect: pr,
                ...cs(play, ['borderRadius', 'width', 'height', 'top', 'left', 'transform', 'backgroundColor']),
                // posición relativa al media (foto):
                offsetInMediaPct: mr ? {
                    left: px((pr.x - mr.x) / mr.w * 100),
                    top: px((pr.y - mr.y) / mr.h * 100),
                    right: px((mr.x + mr.w - (pr.x + pr.w)) / mr.w * 100),
                    bottom: px((mr.y + mr.h - (pr.y + pr.h)) / mr.h * 100),
                } : null,
                centeredInMedia: mr ? {
                    dxFromCenter: px((pr.x + pr.w / 2) - (mr.x + mr.w / 2)),
                    dyFromCenter: px((pr.y + pr.h / 2) - (mr.y + mr.h / 2)),
                } : null,
            };
            // color del triángulo SVG (fill via gradient -> leemos stops del defs)
            const svg = play.querySelector('svg');
            const circle = play.querySelector('circle');
            const stops = [...play.querySelectorAll('stop')].map(s => ({ offset: s.getAttribute('offset'), color: s.getAttribute('stop-color') }));
            out.play.svg = { hasCircle: !!circle, circleFill: circle ? circle.getAttribute('fill') : null, gradientStops: stops, viewBox: svg ? svg.getAttribute('viewBox') : null };
        }
        if (overlay) out.overlay = { rect: rect(overlay), ...cs(overlay, ['backgroundColor', 'opacity']) };
        if (kicker) out.kicker = { text: kicker.textContent.trim(), ...cs(kicker, ['fontFamily', 'fontSize', 'fontWeight', 'color', 'letterSpacing', 'lineHeight', 'textTransform']) };
        if (title) out.title = { text: title.textContent.trim(), ...cs(title, ['fontFamily', 'fontSize', 'fontWeight', 'color', 'lineHeight']) };
        if (quote) out.quote = { ...cs(quote, ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'color', 'lineHeight']) };
        if (qmark) {
            out.qmark = { rect: rect(qmark), ...cs(qmark, ['width', 'height']) };
            const stops = [...qmark.querySelectorAll('stop')].map(s => ({ offset: s.getAttribute('offset'), color: s.getAttribute('stop-color') }));
            out.qmark.gradientStops = stops;
        }
        if (author) {
            out.author = {
                text: author.textContent.replace(/\s+/g, ' ').trim(),
                rect: rect(author),
                ...cs(author, ['textAlign', 'alignSelf', 'fontSize', 'color']),
            };
            const name = author.querySelector('.jlb-testimoniales__author-name');
            out.author.nameWeight = name ? getComputedStyle(name).fontWeight : null;
        }
        if (nav) {
            out.nav = { rect: rect(nav), ...cs(nav, ['display', 'gap', 'marginTop', 'justifyContent']) };
            const arrow = document.querySelector('.jlb-testimoniales__arrow');
            if (arrow) out.nav.arrow = { rect: rect(arrow), ...cs(arrow, ['width', 'height', 'borderRadius']) };
        }
        // Layout relativo: ¿autor y nav en la misma fila?
        if (author && nav) {
            const ar = rect(author), nr = rect(nav);
            out.layoutAuthorNav = {
                authorY: ar.y, navY: nr.y,
                authorRightX: px(ar.x + ar.w), navRightX: px(nr.x + nr.w),
                sameRow: Math.abs((ar.y + ar.h / 2) - (nr.y + nr.h / 2)) < 30,
                authorIsRightOfNav: ar.x > (nr.x + nr.w),
                authorAbove: (ar.y + ar.h) <= nr.y + 5,
            };
        }
        if (body) out.body = { rect: rect(body), ...cs(body, ['padding', 'gap', 'flexDirection', 'justifyContent']) };
        if (decor) {
            out.decor = {
                rect: rect(decor),
                ...cs(decor, ['top', 'right', 'width', 'zIndex']),
            };
            const svg = decor.querySelector('svg');
            const fills = [...decor.querySelectorAll('path')].map(p => p.getAttribute('fill'));
            out.decor.svg = { viewBox: svg ? svg.getAttribute('viewBox') : null, pathCount: decor.querySelectorAll('path').length, fills };
        }
        return out;
    });

    console.log(JSON.stringify(data, null, 2));

    // Errores de consola JS recolectados.
    process.exitCode = 0;
} catch (err) {
    console.error('❌', err.message);
    try { await screenshot(page, 'testi-detalle-error'); } catch {}
    process.exitCode = 1;
} finally {
    await browser.close();
}
