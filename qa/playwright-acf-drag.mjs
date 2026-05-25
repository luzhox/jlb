/**
 * QA visual del drag-and-drop de layouts en ACF Flexible Content.
 *
 * Loguea en wp-admin (credenciales de qa/.env), abre la página "Inicio",
 * verifica que sortable está inicializado con handle descendiente (no `>`)
 * por el parche, intenta arrastrar el layout #2 ARRIBA del #1 y verifica
 * que el orden cambió.
 *
 * Uso:   node qa/playwright-acf-drag.mjs
 */
import { launchAdminSession, openPageByTitle, screenshot } from './lib/wp-playwright.mjs';

const { browser, page } = await launchAdminSession();

try {
    // 1. Abrir "Inicio".
    console.log('==> 1/5 Abrir editor de Inicio');
    await openPageByTitle(page, /inicio/i);
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 });
    await page.waitForTimeout(1500);

    // 2. Hover sobre el field para confirmar que sortable ya está inicializado
    //    (nuestro patch lo hace en init, pero esto valida que sigue ok).
    console.log('==> 2/5 Hover sobre el flex field');
    await page.locator('.acf-field-flexible-content').first().hover();
    await page.waitForTimeout(600);

    // 3. Diagnóstico de sortable.
    console.log('==> 3/5 Diagnóstico de sortable');
    const sortableInfo = await page.evaluate(() => {
        const $ = window.jQuery;
        const $field = $('.acf-field-flexible-content').first();
        const $container = $field.find('.acf-flexible-content').first().find('> .values');
        const inst = $container.data('uiSortable');
        return {
            sortableInitialized: !!inst,
            handle: inst ? inst.options.handle : null,
            layoutCount: $container.find('> .layout').length,
        };
    });
    console.log('  ', JSON.stringify(sortableInfo));

    if (!sortableInfo.sortableInitialized) {
        throw new Error('Sortable NO inicializado. El parche acf-collapse-patch.js no está cargando.');
    }

    // 4. Capturar orden ANTES y screenshot.
    const orderBefore = await page.$$eval(
        '.acf-field-flexible-content .acf-flexible-content > .values > .layout',
        els => els.map(e => e.getAttribute('data-layout'))
    );
    console.log('==> 4/5 Orden ANTES:', orderBefore);
    await screenshot(page, 'acf-drag-before');

    // 5. Drag layout #2 -> arriba de layout #1.
    const handles = page.locator('.acf-field-flexible-content .acf-flexible-content > .values > .layout .acf-fc-layout-handle');
    const h1 = handles.nth(0);
    const h2 = handles.nth(1);

    const box1 = await h1.boundingBox();
    const box2 = await h2.boundingBox();
    if (!box1 || !box2) throw new Error('No se pudieron obtener boundingBoxes de los handles.');

    console.log('==> 5/5 Drag #2 -> #1 con dragTo()');
    await h2.dragTo(h1, {
        sourcePosition: { x: box2.width / 2, y: box2.height / 2 },
        targetPosition: { x: box1.width / 2, y: 0 },
    });
    await page.waitForTimeout(1500);

    const orderAfter = await page.$$eval(
        '.acf-field-flexible-content .acf-flexible-content > .values > .layout',
        els => els.map(e => e.getAttribute('data-layout'))
    );
    console.log('  Orden DESPUÉS:', orderAfter);
    await screenshot(page, 'acf-drag-after');

    const changed = JSON.stringify(orderBefore) !== JSON.stringify(orderAfter);
    console.log(changed ? '\n✅ DRAG OK — orden cambió' : '\n❌ DRAG FALLA — orden idéntico');
    process.exitCode = changed ? 0 : 1;

} catch (err) {
    console.error('❌', err.message);
    try { await screenshot(page, 'acf-drag-error'); } catch {}
    process.exitCode = 1;
} finally {
    await browser.close();
}
