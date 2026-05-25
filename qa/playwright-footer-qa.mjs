/**
 * QA visual del FOOTER del home JLB en vivo vs diseño Figma (nodo 4232:1080).
 *
 * Captura .jlb-footer en 3 viewports (1440 desktop objetivo pixel-perfect,
 * 834 tablet, 390 mobile), SIN admin bar, animaciones OFF, scroll completo
 * previo. Mide estructura, iconos sociales, onda de la barra inferior, labels
 * inline vs heading, tipografía/colores, orden de columnas y legales, subrayados.
 *
 * Uso:  node qa/playwright-footer-qa.mjs
 */
import path from 'node:path';
import fs from 'node:fs';
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const LIVE_DIR = path.join(SCR_DIR, 'live');
if (!fs.existsSync(LIVE_DIR)) fs.mkdirSync(LIVE_DIR, { recursive: true });

const VIEWPORTS = [
    { name: 'desktop', width: 1440, height: 1200 },
    { name: 'tablet',  width: 834,  height: 1100 },
    { name: 'mobile',  width: 390,  height: 1400 },
];

// CSS para neutralizar animaciones (GSAP/AOS dejan opacidades intermedias).
const KILL_ANIM = `
  *, *::before, *::after {
    transition: none !important;
    animation: none !important;
  }
  [data-gsap], [data-aos], .jlb-footer * {
    opacity: 1 !important;
    transform: none !important;
    visibility: visible !important;
  }
`;

const report = { byViewport: {}, consoleErrors: [] };

const { browser, ctx, page } = await launchBrowser({ viewport: VIEWPORTS[0] });

page.on('console', m => {
    if (m.type() === 'error') report.consoleErrors.push(m.text());
});
page.on('pageerror', e => report.consoleErrors.push('pageerror: ' + e.message));

try {
    for (const vp of VIEWPORTS) {
        console.log(`\n==> Viewport ${vp.name} (${vp.width}px)`);
        await page.setViewportSize({ width: vp.width, height: vp.height });
        await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle' });

        // Matar animaciones.
        await page.addStyleTag({ content: KILL_ANIM });

        // Scroll completo para disparar lazy/GSAP, luego bajar al footer.
        await page.evaluate(async () => {
            await new Promise(res => {
                let y = 0;
                const step = () => {
                    window.scrollTo(0, y);
                    y += window.innerHeight;
                    if (y < document.body.scrollHeight) setTimeout(step, 40);
                    else { window.scrollTo(0, document.body.scrollHeight); setTimeout(res, 200); }
                };
                step();
            });
        });
        await page.waitForSelector('.jlb-footer', { timeout: 15000 });
        await page.locator('.jlb-footer').scrollIntoViewIfNeeded();
        await page.waitForTimeout(800);

        // element.screenshot del footer.
        const file = path.join(LIVE_DIR, `footer-${vp.width}-after.png`);
        await page.locator('.jlb-footer').screenshot({ path: file });
        console.log('  📸', file);

        // --- Mediciones del DOM ---
        const data = await page.evaluate(() => {
            const out = {};
            const footer = document.querySelector('.jlb-footer');
            const inner = document.querySelector('.jlb-footer__inner');
            out.footerBox = footer ? footer.getBoundingClientRect() : null;

            // Grid columns.
            if (inner) {
                const cs = getComputedStyle(inner);
                out.gridTemplateColumns = cs.gridTemplateColumns;
                out.gridColumnCount = cs.gridTemplateColumns.split(' ').length;
                out.gap = cs.gap;
                // hijos directos del inner = "columnas" lógicas
                out.innerChildren = [...inner.children].map(c => ({
                    tag: c.tagName.toLowerCase(),
                    cls: c.className,
                    h2: [...c.querySelectorAll('h2')].map(h => h.textContent.trim()),
                    text: c.textContent.replace(/\s+/g, ' ').trim().slice(0, 60),
                }));
            }

            // Headings: familia/tamaño/color.
            const h2s = [...document.querySelectorAll('.jlb-footer h2')].map(h => {
                const cs = getComputedStyle(h2El(h));
                return {
                    text: h.textContent.trim(),
                    fontFamily: cs.fontFamily,
                    fontSize: cs.fontSize,
                    color: cs.color,
                    display: cs.display,
                };
                function h2El(x){return x;}
            });
            out.headings = h2s;

            // ¿"Síguenos en:" y "Escríbenos:" son heading (h2) o label inline?
            out.siguenosIsHeading = !!h2s.find(h => /s[íi]guenos/i.test(h.text));
            out.escribenosIsHeading = !!h2s.find(h => /escr[íi]benos/i.test(h.text));

            // Labels inline .jlb-footer__label (Raleway bold 14px, NO h2).
            out.labels = [...document.querySelectorAll('.jlb-footer__label')].map(l => {
                const cs = getComputedStyle(l);
                return {
                    text: l.textContent.trim(),
                    tag: l.tagName.toLowerCase(),
                    fontFamily: cs.fontFamily.split(',')[0].replace(/["']/g, ''),
                    fontSize: cs.fontSize,
                    fontWeight: cs.fontWeight,
                    color: cs.color,
                };
            });

            // Iconos sociales.
            const socWrap = document.querySelector('.jlb-footer__socials');
            if (socWrap) {
                const links = [...socWrap.querySelectorAll('a')];
                out.socials = links.map(a => {
                    const cs = getComputedStyle(a);
                    const box = a.getBoundingClientRect();
                    const svg = a.querySelector('svg');
                    const img = a.querySelector('img');
                    return {
                        text: a.textContent.trim(),
                        hasSvg: !!svg,
                        hasImg: !!img,
                        innerHTML: a.innerHTML.replace(/\s+/g, ' ').trim().slice(0, 80),
                        width: Math.round(box.width),
                        height: Math.round(box.height),
                        borderRadius: cs.borderRadius,
                        background: cs.backgroundImage.slice(0, 80) || cs.backgroundColor,
                        color: cs.color,
                    };
                });
            }

            // Barra inferior: onda SVG + gradiente.
            const bottom = document.querySelector('.jlb-footer__bottom');
            if (bottom) {
                const cs = getComputedStyle(bottom);
                const wave = document.querySelector('.jlb-footer__wave');
                const wavePath = wave ? wave.querySelector('path') : null;
                let waveBox = null, wavePathD = null, waveVisible = false;
                if (wave) {
                    const wcs = getComputedStyle(wave);
                    waveBox = wave.getBoundingClientRect();
                    waveVisible = wcs.display !== 'none' && wcs.visibility !== 'hidden' && waveBox.height > 1;
                }
                if (wavePath) wavePathD = wavePath.getAttribute('d');
                out.bottom = {
                    background: cs.backgroundImage.slice(0, 140),
                    color: cs.color,
                    hasWaveSvg: !!wave,
                    hasWavePath: !!wavePath,
                    waveVisible,
                    waveHeight: waveBox ? Math.round(waveBox.height) : null,
                    waveWidth: waveBox ? Math.round(waveBox.width) : null,
                    wavePathD,
                };
                // links legales en orden DOM (solo dentro de .jlb-footer__legal).
                const legalNav = bottom.querySelector('.jlb-footer__legal') || bottom;
                out.legalOrder = [...legalNav.querySelectorAll('a')].map(a => a.textContent.trim());
                out.copyright = (bottom.querySelector('p')?.textContent || '').trim();
                out.copyrightHasEnDash = /–/.test(out.copyright);
            }

            // Subrayado de teléfonos y email.
            const phoneLinks = [...document.querySelectorAll('.jlb-footer__phone a')];
            out.phoneUnderline = phoneLinks.slice(0, 3).map(a => ({
                text: a.textContent.trim(),
                textDecoration: getComputedStyle(a).textDecorationLine,
            }));
            const emailLink = document.querySelector('.jlb-footer a[href^="mailto:"]');
            out.emailUnderline = emailLink ? {
                text: emailLink.textContent.trim(),
                textDecoration: getComputedStyle(emailLink).textDecorationLine,
            } : null;

            // Color de texto general.
            const p = document.querySelector('.jlb-footer__inner p');
            if (p) out.bodyColor = getComputedStyle(p).color;

            // Overflow horizontal (mobile/tablet no se rompe).
            out.docScrollW = document.documentElement.scrollWidth;
            out.winInnerW = window.innerWidth;
            out.hasHorizontalOverflow = document.documentElement.scrollWidth > window.innerWidth + 1;

            return out;
        });

        report.byViewport[vp.name] = { width: vp.width, ...data };
        console.log('  grid-template-columns:', data.gridTemplateColumns, '| cols:', data.gridColumnCount);
        console.log('  inner children (columnas lógicas):', data.innerChildren?.length);
        if (data.socials) console.log('  socials:', JSON.stringify(data.socials));
        console.log('  Síguenos es heading?', data.siguenosIsHeading, '| Escríbenos es heading?', data.escribenosIsHeading);
        if (data.labels) console.log('  labels inline:', JSON.stringify(data.labels));
        if (data.bottom) {
            console.log('  wave: hasSvg=' + data.bottom.hasWaveSvg, 'hasPath=' + data.bottom.hasWavePath, 'visible=' + data.bottom.waveVisible, 'h=' + data.bottom.waveHeight);
            console.log('  bottom background:', data.bottom.background);
            console.log('  legalOrder:', JSON.stringify(data.legalOrder));
            console.log('  copyright:', data.copyright, '| en-dash?', data.bottom.copyrightHasEnDash);
        }
        console.log('  phoneUnderline:', JSON.stringify(data.phoneUnderline));
        console.log('  emailUnderline:', JSON.stringify(data.emailUnderline));
        console.log('  overflow horizontal?', data.hasHorizontalOverflow, `(scrollW=${data.docScrollW} win=${data.winInnerW})`);
    }

    // Dump report JSON.
    const repFile = path.join(LIVE_DIR, 'footer-qa-report.json');
    fs.writeFileSync(repFile, JSON.stringify(report, null, 2));
    console.log('\n📄', repFile);
    console.log('\nconsoleErrors:', report.consoleErrors.length ? report.consoleErrors : 'ninguno');

} catch (err) {
    console.error('❌', err.message);
    try { await page.locator('.jlb-footer').screenshot({ path: path.join(LIVE_DIR, 'footer-error.png') }); } catch {}
    process.exitCode = 1;
} finally {
    await browser.close();
}
