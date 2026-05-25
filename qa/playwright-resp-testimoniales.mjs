/**
 * QA visual responsive — diagnóstico de DOS secciones del home JLB en vivo:
 *   1. .jlb-testimoniales  (slider de testimoniales)
 *   2. .jlb-testimonial    (carrusel testimonio de padres)
 *
 * Captura SIN admin bar (sesión pública / deslogueada), con animaciones
 * desactivadas, tras scroll completo (dispara GSAP/Swiper/lazy). Para cada
 * sección × viewport: element.screenshot() a qa/live/resp-<sec>-<w>.png +
 * mediciones (overflow, font-sizes, bounding boxes, dots, foto) + errores JS.
 *
 * NO edita código. Solo reporta. Resultado JSON a stdout (parseado por el report).
 *
 * Uso:  node qa/playwright-resp-testimoniales.mjs
 */
import path from 'node:path';
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const LIVE_DIR = path.join(SCR_DIR, 'live');

// Sufijo opcional para los nombres de archivo: `node ... fixed` → `-fixed`.
const SUFFIX = process.argv[2] ? `-${process.argv[2]}` : '';

const VIEWPORTS = [
    { label: 'desktop', width: 1440, height: 900  },
    { label: 'tablet',  width: 834,  height: 1112 },
    { label: 'tablet',  width: 768,  height: 1024 },
    { label: 'mobile',  width: 390,  height: 844  },
    { label: 'mobile',  width: 360,  height: 800  },
];

const SECTIONS = [
    { key: 'testimoniales', sel: '.jlb-testimoniales' },
    { key: 'padres',        sel: '.jlb-testimonial'   },
];

// CSS que mata animaciones/transiciones para capturas estables.
const KILL_ANIM = `*,*::before,*::after{animation:none!important;transition:none!important;animation-duration:0s!important;animation-delay:0s!important;transition-duration:0s!important;}`;

const results = { site: WP_SITE_URL, viewports: [] };

for (const vp of VIEWPORTS) {
    const tag = `${vp.label} ${vp.width}×${vp.height}`;
    console.log(`\n========== ${tag} ==========`);

    const { browser, ctx, page } = await launchBrowser({
        viewport: { width: vp.width, height: vp.height },
    });

    // Recolecta errores de consola JS de este viewport.
    const consoleErrors = [];
    page.on('console', m => {
        if (m.type() === 'error') consoleErrors.push(m.text());
    });
    page.on('pageerror', e => consoleErrors.push('pageerror: ' + e.message));

    const vpResult = { label: vp.label, width: vp.width, height: vp.height, sections: {}, consoleErrors, docOverflow: null };

    try {
        await page.goto(WP_SITE_URL + '/', { waitUntil: 'networkidle', timeout: 45000 });

        // Mata animaciones tras cargar (evita el race de document.head null).
        await page.addStyleTag({ content: KILL_ANIM });

        // Scroll completo en pasos para disparar GSAP ScrollTrigger + lazy + Swiper init.
        await page.evaluate(async () => {
            const step = Math.round(window.innerHeight * 0.6);
            const max = document.body.scrollHeight;
            for (let y = 0; y <= max; y += step) {
                window.scrollTo(0, y);
                await new Promise(r => setTimeout(r, 120));
            }
            window.scrollTo(0, document.body.scrollHeight);
            await new Promise(r => setTimeout(r, 400));
            window.scrollTo(0, 0);
            await new Promise(r => setTimeout(r, 200));
        });
        await page.waitForTimeout(800);

        // Re-fuerza visibilidad de elementos GSAP (por si quedaron a opacity:0 sin trigger).
        await page.addStyleTag({ content: `[data-gsap],[data-gsap-batch]{opacity:1!important;transform:none!important;}` });
        await page.waitForTimeout(300);

        // ¿Overflow horizontal del documento?
        vpResult.docOverflow = await page.evaluate(() => {
            const de = document.documentElement;
            return {
                scrollWidth: de.scrollWidth,
                clientWidth: de.clientWidth,
                overflowPx: de.scrollWidth - de.clientWidth,
                hasHOverflow: de.scrollWidth > de.clientWidth,
            };
        });
        console.log('  doc overflow:', vpResult.docOverflow);

        // Identifica el/los elementos más anchos que el viewport (culpable del overflow).
        vpResult.widestCulprits = await page.evaluate(vw => {
            const px = v => Math.round(v);
            const out = [];
            document.querySelectorAll('body *').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.width > vw + 50) {
                    out.push({
                        sel: (el.className && typeof el.className === 'string'
                            ? el.tagName.toLowerCase() + '.' + el.className.trim().split(/\s+/).slice(0, 2).join('.')
                            : el.tagName.toLowerCase()),
                        w: px(r.width),
                        scrollW: el.scrollWidth,
                        section: el.closest('.jlb-testimonial') ? 'padres'
                            : el.closest('.jlb-testimoniales') ? 'testimoniales' : 'otro',
                    });
                }
            });
            // Ordena por ancho desc y dedup por selector, top 8.
            const seen = new Set();
            return out.sort((a, b) => b.w - a.w).filter(o => {
                if (seen.has(o.sel)) return false; seen.add(o.sel); return true;
            }).slice(0, 8);
        }, vp.width);
        console.log('  culpables overflow:', JSON.stringify(vpResult.widestCulprits));

        for (const section of SECTIONS) {
            console.log(`  -- sección ${section.key} (${section.sel})`);
            const loc = page.locator(section.sel).first();
            const count = await page.locator(section.sel).count();
            if (count === 0) {
                console.log('     ❌ no existe en el DOM');
                vpResult.sections[section.key] = { found: false };
                continue;
            }

            await loc.scrollIntoViewIfNeeded();
            await page.waitForTimeout(400);

            // Screenshot del elemento.
            const fname = `resp-${section.key}-${vp.width}${SUFFIX}.png`;
            const fpath = path.join(LIVE_DIR, fname);
            try {
                await loc.screenshot({ path: fpath });
                console.log('     📸', fpath);
            } catch (e) {
                console.log('     ⚠️ screenshot falló:', e.message);
            }

            // Mediciones por sección.
            const measure = await loc.evaluate((root, vw) => {
                const px = v => Math.round(v * 100) / 100;
                const fs = el => el ? px(parseFloat(getComputedStyle(el).fontSize)) : null;
                const rect = el => {
                    if (!el) return null;
                    const r = el.getBoundingClientRect();
                    return { x: px(r.x), y: px(r.y), w: px(r.width), h: px(r.height), right: px(r.right) };
                };

                const rootRect = root.getBoundingClientRect();

                // Elementos que desbordan el viewport horizontalmente (dentro de la sección).
                const overflowing = [];
                root.querySelectorAll('*').forEach(el => {
                    const r = el.getBoundingClientRect();
                    if (r.width === 0 || r.height === 0) return;
                    if (r.right > vw + 1 || r.left < -1) {
                        overflowing.push({
                            sel: el.className && typeof el.className === 'string'
                                ? '.' + el.className.trim().split(/\s+/).slice(0, 2).join('.')
                                : el.tagName.toLowerCase(),
                            left: px(r.left), right: px(r.right), w: px(r.width),
                        });
                    }
                });
                // Dedup por selector.
                const seen = new Set();
                const overflowDedup = overflowing.filter(o => {
                    if (seen.has(o.sel)) return false;
                    seen.add(o.sel); return true;
                }).slice(0, 12);

                const out = {
                    found: true,
                    rootRect: rect(root),
                    sectionOverflow: { rootRight: px(rootRect.right), vw, exceedsVw: rootRect.right > vw + 1 },
                    overflowing: overflowDedup,
                };

                // Específico testimoniales.
                if (root.classList.contains('jlb-testimoniales')) {
                    const firstSlide = root.querySelector('.jlb-testimoniales__slide');
                    const media = root.querySelector('.jlb-testimoniales__media');
                    const mediaImg = root.querySelector('.jlb-testimoniales__media img');
                    const body = root.querySelector('.jlb-testimoniales__body');
                    const decor = root.querySelector('.jlb-testimoniales__decor');
                    const nav = root.querySelector('.jlb-testimoniales__nav');
                    const play = root.querySelector('.jlb-testimoniales__play');
                    const arrows = root.querySelectorAll('.jlb-testimoniales__arrow');

                    out.testimoniales = {
                        slideGridCols: firstSlide ? getComputedStyle(firstSlide).gridTemplateColumns : null,
                        slideDisplay: firstSlide ? getComputedStyle(firstSlide).display : null,
                        media: rect(media),
                        mediaImg: rect(mediaImg),
                        mediaCS: media ? {
                            minHeight: getComputedStyle(media).minHeight,
                            maxHeight: getComputedStyle(media).maxHeight,
                            aspectRatio: getComputedStyle(media).aspectRatio,
                        } : null,
                        mediaVisible: media ? (media.getBoundingClientRect().height > 10) : false,
                        body: rect(body),
                        decor: decor ? { rect: rect(decor), display: getComputedStyle(decor).display } : null,
                        nav: rect(nav),
                        navCount: arrows.length,
                        play: play ? { rect: rect(play), display: getComputedStyle(play).display } : null,
                        fontSizes: {
                            title: fs(root.querySelector('.jlb-testimoniales__title')),
                            quote: fs(root.querySelector('.jlb-testimoniales__quote p')),
                            author: fs(root.querySelector('.jlb-testimoniales__author')),
                            kicker: fs(root.querySelector('.jlb-testimoniales__kicker')),
                        },
                    };
                    // ¿foto encima del body? (stacking en 1-col debería ser foto arriba, body abajo)
                    if (media && body) {
                        const mr = media.getBoundingClientRect(), br = body.getBoundingClientRect();
                        out.testimoniales.stacked = mr.bottom <= br.top + 2;
                        out.testimoniales.mediaBodyOverlap = px(Math.max(0, mr.bottom - br.top));
                    }
                }

                // Específico padres.
                if (root.classList.contains('jlb-testimonial')) {
                    const inner = root.querySelector('.jlb-testimonial__inner');
                    const head = root.querySelector('.jlb-testimonial__head');
                    const carousel = root.querySelector('.jlb-testimonial__carousel');
                    const dotsWrap = root.querySelector('.jlb-testimonial__dots');
                    const dots = root.querySelectorAll('.jlb-testimonial__dot');
                    const quoteMark = root.querySelector('.jlb-testimonial__quote-mark');
                    const blockquote = root.querySelector('.jlb-testimonial__slide blockquote');
                    const firstSlide = root.querySelector('.jlb-testimonial__slide');

                    out.padres = {
                        innerGridCols: inner ? getComputedStyle(inner).gridTemplateColumns : null,
                        head: rect(head),
                        carousel: rect(carousel),
                        // ¿apilado? head arriba, carousel abajo.
                        stacked: (head && carousel) ? (head.getBoundingClientRect().bottom <= carousel.getBoundingClientRect().top + 4) : null,
                        dotsWrap: dotsWrap ? { rect: rect(dotsWrap), display: getComputedStyle(dotsWrap).display } : null,
                        dotsCount: dots.length,
                        quoteMark: quoteMark ? rect(quoteMark) : null,
                        blockquote: rect(blockquote),
                        // ¿la comilla se sale del slide o tapa el texto? (mide gap comilla→p)
                        quoteToTextGap: (quoteMark && blockquote) ? px(blockquote.querySelector('p')?.getBoundingClientRect().top - quoteMark.getBoundingClientRect().bottom) : null,
                        slideHeight: firstSlide ? px(firstSlide.getBoundingClientRect().height) : null,
                        fontSizes: {
                            title: fs(root.querySelector('.jlb-testimonial__head h2')),
                            quote: fs(root.querySelector('.jlb-testimonial__slide blockquote p')),
                            cite: fs(root.querySelector('.jlb-testimonial__slide cite')),
                            kicker: fs(root.querySelector('.jlb-kicker')),
                        },
                    };
                }

                return out;
            }, vp.width);

            vpResult.sections[section.key] = measure;
            console.log('     medición:', JSON.stringify(measure[section.key === 'testimoniales' ? 'testimoniales' : 'padres'] || {}, null, 0).slice(0, 400));
            if (measure.overflowing && measure.overflowing.length) {
                console.log('     ⚠️ elementos que desbordan:', JSON.stringify(measure.overflowing));
            }
        }

        // No-regresión desktop: captura completa del viewport con ambas secciones.
        if (vp.label === 'desktop') {
            await page.evaluate(() => {
                const sec = document.querySelector('.jlb-testimoniales');
                if (sec) sec.scrollIntoView({ block: 'start' });
            });
            await page.waitForTimeout(300);
            const dfile = path.join(LIVE_DIR, `resp-desktop-${vp.width}${SUFFIX}.png`);
            await page.screenshot({ path: dfile });
            console.log('     📸 desktop no-regresión:', dfile);
        }

        // ¿Solapamiento con header sticky? Mide top de cada sección vs alto del header.
        vpResult.header = await page.evaluate(() => {
            const h = document.querySelector('header, .jlb-header, #masthead, .site-header');
            if (!h) return null;
            const cs = getComputedStyle(h);
            const r = h.getBoundingClientRect();
            return { tag: h.className || h.tagName, position: cs.position, height: Math.round(r.height), zIndex: cs.zIndex };
        });

    } catch (err) {
        console.error('  ❌ error en viewport:', err.message);
        vpResult.error = err.message;
        try {
            await page.screenshot({ path: path.join(LIVE_DIR, `resp-ERROR-${vp.width}.png`), fullPage: false });
        } catch {}
    } finally {
        if (consoleErrors.length) console.log('  [console errors]', consoleErrors.length, consoleErrors.slice(0, 5));
        else console.log('  [console errors] ninguno');
        results.viewports.push(vpResult);
        await browser.close();
    }
}

// Volcado JSON final para construir el reporte.
const outJson = path.join(LIVE_DIR, `resp-report${SUFFIX}.json`);
const fs = await import('node:fs');
fs.writeFileSync(outJson, JSON.stringify(results, null, 2));
console.log('\n📄 JSON:', outJson);
