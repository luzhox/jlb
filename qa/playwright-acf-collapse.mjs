/**
 * QA visual del parche de collapse de ACF Flexible Content.
 *
 * Loguea en wp-admin (credenciales de qa/.env), abre la página "Inicio" con
 * módulos cargados, toma screenshot ANTES, hace click en colapsar el primer
 * layout, screenshot DESPUÉS. Verifica que la clase `-collapsed` se aplica
 * y los campos `.acf-fields` quedan ocultos.
 *
 * Uso:   node qa/playwright-acf-collapse.mjs
 */
import path from 'node:path';
import { launchAdminSession, openPageByTitle, screenshot, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs';

const { browser, page } = await launchAdminSession();

try {
    // 1. Abrir "Inicio".
    console.log('==> 1/4 Abrir editor de Inicio');
    await openPageByTitle(page, /inicio/i);
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 });
    await page.waitForTimeout(1500);

    // 2. Screenshot ANTES.
    console.log('==> 2/4 Screenshot ANTES');
    await page.locator('.acf-field-flexible-content').first().scrollIntoViewIfNeeded();
    await page.waitForTimeout(400);
    await screenshot(page, 'acf-collapse-before');

    // 3. Click en el ícono de colapsar del primer layout REAL (no clones).
    console.log('==> 3/4 Click en colapsar primer layout');
    const firstLayout = page.locator('.acf-field-flexible-content .acf-flexible-content > .values > .layout').first();
    const collapseBtn = firstLayout.locator('[data-name="collapse-layout"]').first();

    await collapseBtn.scrollIntoViewIfNeeded();
    await collapseBtn.click({ force: true });
    await page.waitForTimeout(600);

    const cls = await firstLayout.getAttribute('class');
    const hasCollapsed = (cls || '').split(/\s+/).includes('-collapsed');
    console.log('  clases tras click:', cls);
    console.log('  ¿tiene -collapsed?', hasCollapsed ? '✓' : '✗');

    // 4. Screenshot DESPUÉS + verificación de fields ocultos.
    console.log('==> 4/4 Screenshot DESPUÉS + verificación CSS');
    await firstLayout.scrollIntoViewIfNeeded();
    await page.waitForTimeout(300);
    await screenshot(page, 'acf-collapse-after');

    const box = await firstLayout.boundingBox();
    if (box) {
        await page.screenshot({
            path: path.join(SCR_DIR, 'acf-collapse-after-cropped.png'),
            clip: {
                x: Math.max(0, box.x - 20),
                y: Math.max(0, box.y - 20),
                width: Math.min(1440, box.width + 40),
                height: box.height + 40,
            },
        });
        console.log('📸', path.join(SCR_DIR, 'acf-collapse-after-cropped.png'));
    }

    const fieldsHidden = await firstLayout.evaluate(layout => {
        const fields = layout.querySelector('.acf-fields');
        if (!fields) return { error: 'No .acf-fields descendant found' };
        const cs = getComputedStyle(fields);
        return { display: cs.display, hidden: cs.display === 'none' };
    });
    console.log('  .acf-fields computed display:', fieldsHidden);

    const ok = hasCollapsed && fieldsHidden && fieldsHidden.hidden;
    console.log(ok ? '\n✅ COLLAPSE OK' : '\n❌ COLLAPSE FALLA');
    process.exitCode = ok ? 0 : 1;

} catch (err) {
    console.error('❌', err.message);
    try { await screenshot(page, 'acf-collapse-error'); } catch {}
    process.exitCode = 1;
} finally {
    await browser.close();
}
