/**
 * Diagnóstico fino: contenido del repeater `slides` del layout jlb_hero en el
 * editor del home. ¿Cuántas filas? ¿Qué valores (titulo/texto/imagen) tienen?
 * Esto decide si el hero retorna vacío en el front por slides sin contenido.
 * Solo diagnóstico — NO edita.
 */
import { launchAdminSession, openPageByTitle } from './lib/wp-playwright.mjs';

const { browser, page } = await launchAdminSession();
try {
    await openPageByTitle(page, /inicio/i);
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 });
    await page.waitForTimeout(1500);

    const info = await page.evaluate(() => {
        const heroLayout = [...document.querySelectorAll('.acf-flexible-content > .values > .layout')]
            .find(l => l.getAttribute('data-layout') === 'jlb_hero');
        if (!heroLayout) return { error: 'no jlb_hero layout' };

        const slidesRepeater = heroLayout.querySelector('[data-name="slides"]');
        if (!slidesRepeater) return { error: 'no slides repeater' };

        const rows = [...slidesRepeater.querySelectorAll('.acf-row:not(.acf-clone)')];
        const readVal = (row, name) => {
            const field = row.querySelector(`[data-name="${name}"]`);
            if (!field) return '(sin campo)';
            const inp = field.querySelector('input[type="text"], textarea, input:not([type="hidden"])');
            if (inp) return (inp.value || '').slice(0, 60);
            // imagen: ACF image field guarda en input hidden + img preview
            const hidden = field.querySelector('input[type="hidden"]');
            const img = field.querySelector('img');
            return { hiddenVal: hidden ? hidden.value : null, hasImgPreview: !!img };
        };
        return {
            rowCount: rows.length,
            rows: rows.map((r, i) => ({
                idx: i,
                titulo: readVal(r, 'titulo'),
                texto: readVal(r, 'texto'),
                eyebrow: readVal(r, 'eyebrow'),
                imagen: readVal(r, 'imagen'),
            })),
        };
    });
    console.log(JSON.stringify(info, null, 2));
} catch (e) {
    console.error('❌', e.message);
    process.exitCode = 1;
} finally {
    await browser.close();
}
