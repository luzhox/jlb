/**
 * Diagnóstico en wp-admin: ¿el módulo jlb_hero del home tiene el repeater
 * `slides` poblado, o quedó con datos viejos (campos planos) tras convertir a
 * carousel? Abre el editor de "Inicio", localiza el layout jlb_hero dentro del
 * flexible content y reporta qué sub-campos/filas tiene.
 * Solo diagnóstico — NO edita datos ni código.
 */
import { launchAdminSession, openPageByTitle } from './lib/wp-playwright.mjs';

const { browser, page } = await launchAdminSession();
try {
    await openPageByTitle(page, /inicio/i);
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 });
    await page.waitForTimeout(1500);

    const info = await page.evaluate(() => {
        // Buscar el layout cuyo data-layout sea jlb_hero (o que contenga "hero").
        const layouts = [...document.querySelectorAll('.acf-field-flexible-content .acf-flexible-content > .values > .layout')];
        const found = layouts.map(l => {
            const name = l.getAttribute('data-layout');
            // ¿tiene un sub-field "slides" (repeater)?
            const slidesRepeater = l.querySelector('[data-name="slides"]');
            const slidesRows = slidesRepeater ? slidesRepeater.querySelectorAll('.acf-row:not(.acf-clone)').length : null;
            // ¿tiene campos planos viejos (titulo/texto/imagen) a nivel del layout?
            const flatTitulo = !!l.querySelector(':scope [data-name="titulo"]');
            const flatImagen = !!l.querySelector(':scope [data-name="imagen"]');
            return { name, hasSlidesRepeater: !!slidesRepeater, slidesRows, flatTitulo, flatImagen };
        });
        return { layoutCount: layouts.length, layouts: found };
    });
    console.log(JSON.stringify(info, null, 2));
} catch (e) {
    console.error('❌', e.message);
    process.exitCode = 1;
} finally {
    await browser.close();
}
