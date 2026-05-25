/**
 * QA visual — chrome JLB en plantillas internas.
 *
 * Valida que el header JLB (.jlb-header) y el footer JLB (.jlb-footer) aparecen
 * en las plantillas internas (page / single / 404 / blog index) igual que en el
 * home (front-page), y que NO aparece el chrome viejo "Kresna"
 * (#masthead / header.site-header / footer.site-footer).
 *
 * Por cada vista asierta en el DOM:
 *   - EXISTE header.jlb-header y footer.jlb-footer.
 *   - NO existe #masthead, header.site-header, ni footer.site-footer.
 *   - <body> tiene clase jlb-home-template.
 *   - Hay exactamente UN <main id="contenido">.
 * Captura errores de consola por vista.
 *
 * Uso:   node qa/playwright-jlb-chrome.mjs
 */
import { launchAdminSession, screenshot, WP_SITE_URL } from './lib/wp-playwright.mjs';

const SITE = WP_SITE_URL;

/** Asierta el chrome JLB sobre la página actual; devuelve el detalle. */
async function assertChrome(page) {
    return page.evaluate(() => {
        const q = (sel) => document.querySelectorAll(sel).length;
        const bodyClasses = Array.from(document.body.classList);
        return {
            jlbHeader: q('header.jlb-header'),
            jlbFooter: q('footer.jlb-footer'),
            oldMasthead: q('#masthead'),
            oldSiteHeader: q('header.site-header'),
            oldSiteFooter: q('footer.site-footer'),
            hasHomeTemplateClass: bodyClasses.includes('jlb-home-template'),
            bodyClasses,
            mainContenido: q('main#contenido'),
            // detecta tokens sin resolver tipo `color: ;` mirando el header inline.
            title: document.title,
        };
    });
}

/** Evalúa el veredicto de una vista a partir del detalle del DOM. */
function verdict(d) {
    const checks = {
        'header.jlb-header existe (==1)': d.jlbHeader === 1,
        'footer.jlb-footer existe (==1)': d.jlbFooter === 1,
        'NO #masthead': d.oldMasthead === 0,
        'NO header.site-header': d.oldSiteHeader === 0,
        'NO footer.site-footer': d.oldSiteFooter === 0,
        'body.jlb-home-template': d.hasHomeTemplateClass === true,
        'UN solo main#contenido (==1)': d.mainContenido === 1,
    };
    const ok = Object.values(checks).every(Boolean);
    return { ok, checks };
}

const results = {};

const { browser, ctx, page } = await launchAdminSession();

// Buffer de errores/warnings de consola por vista (la lib ya hace forward a stdout,
// pero queremos acumular por vista para el reporte).
let consoleBuffer = [];
page.on('console', m => {
    if (m.type() === 'error' || m.type() === 'warning') {
        consoleBuffer.push(`[${m.type()}] ${m.text()}`);
    }
});
page.on('pageerror', e => consoleBuffer.push(`[pageerror] ${e.message}`));

/** Navega a url, espera, asierta, captura. */
async function runView(key, url, screenshotName) {
    console.log(`\n==> ${key}: ${url}`);
    consoleBuffer = [];
    const resp = await page.goto(url, { waitUntil: 'networkidle' });
    const status = resp ? resp.status() : null;
    await page.waitForTimeout(1200);
    await screenshot(page, screenshotName);
    const detail = await assertChrome(page);
    const v = verdict(detail);
    results[key] = { url, status, detail, ...v, console: [...consoleBuffer] };
    console.log(`  HTTP ${status} | título: ${detail.title}`);
    for (const [name, pass] of Object.entries(v.checks)) {
        console.log(`  ${pass ? '✅' : '❌'} ${name}`);
    }
    if (!v.ok) {
        console.log('  detalle:', JSON.stringify(detail, null, 2));
    }
    if (consoleBuffer.length) {
        console.log(`  consola (${consoleBuffer.length}):`);
        consoleBuffer.forEach(l => console.log('    ', l));
    } else {
        console.log('  consola: sin errores/warnings');
    }
    return v.ok;
}

try {
    // ── Descubrir URLs reales desde wp-admin ───────────────────────────────
    // Front-page (home) primero como baseline.
    // Página interna: cualquier page publicada distinta de la front-page.
    // Post: cualquier entrada publicada.

    console.log('==> Descubriendo front-page, página interna y post');

    // Lee el ID de la front-page desde Ajustes > Lectura.
    await page.goto(`${SITE}/wp-admin/options-reading.php`, { waitUntil: 'domcontentloaded' });
    const frontPageId = await page.evaluate(() => {
        const sel = document.querySelector('#page_on_front');
        return sel ? sel.value : '0';
    });
    console.log('  front-page ID:', frontPageId);

    // Lista de páginas publicadas (título + edit link + post id).
    await page.goto(`${SITE}/wp-admin/edit.php?post_type=page&post_status=publish`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('table.wp-list-table tbody tr');
    const pages = await page.$$eval('table.wp-list-table tbody tr', rows =>
        rows.map(tr => {
            const a = tr.querySelector('a.row-title');
            const id = tr.id ? tr.id.replace('post-', '') : '';
            return a ? { id, title: a.textContent.trim() } : null;
        }).filter(Boolean)
    );
    console.log('  páginas publicadas:', pages.map(p => `${p.id}:${p.title}`).join(', ') || '(ninguna)');

    const internalPage = pages.find(p => p.id !== String(frontPageId));
    if (!internalPage) throw new Error('No hay página interna publicada distinta de la front-page.');
    const internalPageUrl = `${SITE}/?page_id=${internalPage.id}`;
    console.log('  página interna elegida:', `${internalPage.id}:${internalPage.title}`, '→', internalPageUrl);

    // Lista de posts publicados.
    await page.goto(`${SITE}/wp-admin/edit.php?post_type=post&post_status=publish`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('table.wp-list-table tbody tr');
    const posts = await page.$$eval('table.wp-list-table tbody tr', rows =>
        rows.map(tr => {
            const a = tr.querySelector('a.row-title');
            const id = tr.id ? tr.id.replace('post-', '') : '';
            return a ? { id, title: a.textContent.trim() } : null;
        }).filter(Boolean)
    );
    console.log('  posts publicados:', posts.map(p => `${p.id}:${p.title}`).join(', ') || '(ninguno)');
    const singlePost = posts[0] || null;
    const singleUrl = singlePost ? `${SITE}/?p=${singlePost.id}` : null;
    if (singleUrl) console.log('  post elegido:', `${singlePost.id}:${singlePost.title}`, '→', singleUrl);

    // ── Vistas ─────────────────────────────────────────────────────────────
    await runView('home (baseline)', `${SITE}/`, 'chrome-home');
    await runView('page interna', internalPageUrl, 'chrome-page');
    if (singleUrl) {
        await runView('single (post)', singleUrl, 'chrome-single');
    } else {
        console.log('\n⚠️ No hay posts publicados — se omite vista single.');
        results['single (post)'] = { skipped: true, reason: 'No hay posts publicados.' };
    }
    // Blog index: ?page_for_posts si está configurado, sino la home muestra posts.
    // Forzamos el archivo de posts vía feed estándar: el listado de categoría/blog.
    await runView('blog index', `${SITE}/?post_type=post`, 'chrome-blog');
    await runView('404', `${SITE}/esta-pagina-no-existe-xyz/`, 'chrome-404');

    // ── Resumen ──────────────────────────────────────────────────────────────
    console.log('\n================ RESUMEN ================');
    let allOk = true;
    for (const [key, r] of Object.entries(results)) {
        if (r.skipped) {
            console.log(`⚠️  ${key}: OMITIDA (${r.reason})`);
            continue;
        }
        console.log(`${r.ok ? '✅' : '❌'} ${key} (HTTP ${r.status})`);
        if (!r.ok) allOk = false;
    }
    process.exitCode = allOk ? 0 : 1;

} catch (err) {
    console.error('❌', err.message);
    try { await screenshot(page, 'chrome-error'); } catch {}
    process.exitCode = 1;
} finally {
    await browser.close();
}
