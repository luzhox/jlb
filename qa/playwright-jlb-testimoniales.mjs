/**
 * QA visual — módulo jlb-testimoniales.
 *
 * Tres casos validados:
 *   1) SINGLE sin video    → /              (front-page.php tiene 1 ítem demo)
 *   2) MULTI (2 ítems)     → /qa-testimoniales-multi/   (creada vía wp-admin)
 *   3) WITH PLAY (con video) → /qa-testimoniales-multi/ (mismo post, primer ítem con video_url)
 *
 * Los casos 2 y 3 se materializan poblando una página "QA Testimoniales" en
 * wp-admin (Componentes de Página → JLB · Testimoniales (slider)). Si el
 * layout no existe en el dropdown ACF, el script aborta con BLOQUEANTE.
 *
 * Screenshots se guardan en qa/screenshots/.
 */
import path from 'node:path'
import fs from 'node:fs'
import { launchBrowser, launchAdminSession, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs'

const SCREENSHOTS_DIR = path.join(SCR_DIR, 'screenshots')
if (!fs.existsSync(SCREENSHOTS_DIR)) fs.mkdirSync(SCREENSHOTS_DIR, { recursive: true })

const VIEWPORTS = [
    { name: 'desktop-1440', width: 1440, height: 900 },
    { name: 'desktop-1280', width: 1280, height: 800 },
    { name: 'tablet-980',   width: 980,  height: 768 },
    { name: 'mobile-640',   width: 640,  height: 900 },
    { name: 'mobile-375',   width: 375,  height: 800 },
]

const results = {
    screenshots: [],
    asserts: [],
    consoleErrors: [],
    pageErrors: [],
    notes: [],
    blockers: [],
}

function record(name, ok, detail = '') {
    results.asserts.push({ name, ok, detail })
    console.log(`  ${ok ? '✓' : '✗'} ${name}${detail ? ` — ${detail}` : ''}`)
}

async function waitForReady(page, label = '') {
    try {
        await page.waitForSelector('.jlb-testimoniales', { timeout: 15000 })
    } catch {
        results.notes.push(`[${label}] No se encontró .jlb-testimoniales tras 15s.`)
        throw new Error('jlb-testimoniales no presente')
    }
    await page.evaluate(() => document.fonts && document.fonts.ready)
    await page.evaluate(() => {
        const el = document.querySelector('.jlb-testimoniales')
        if (el) el.scrollIntoView({ behavior: 'instant', block: 'center' })
    })
    await page.waitForTimeout(1500)
}

// ── Asserts comunes (case 1: single, sin video, sin nav, sin play) ─────────
async function runSingleAsserts(page, prefix) {
    console.log(`\n→ ${prefix} — Asserts DOM (single, viewport 1440):`)

    const sectionCount = await page.locator('.jlb-testimoniales').count()
    record(`${prefix}: section .jlb-testimoniales existe`, sectionCount >= 1, `count=${sectionCount}`)

    const slideCount = await page.locator('.jlb-testimoniales__slide').count()
    record(`${prefix}: al menos 1 .jlb-testimoniales__slide`, slideCount >= 1, `count=${slideCount}`)

    const kicker = (await page.locator('.jlb-testimoniales__kicker').first().textContent() || '').trim()
    record(`${prefix}: kicker contiene "Testimoniales"`, /testimoniales/i.test(kicker), `"${kicker}"`)

    const title = (await page.locator('.jlb-testimoniales__title').first().textContent() || '').trim()
    record(
        `${prefix}: título contiene "Me motivaron a conseguir mis objetivos"`,
        /me motivaron a conseguir mis objetivos/i.test(title),
        `"${title}"`,
    )

    const authorTxt = (await page.locator('.jlb-testimoniales__author').first().textContent() || '').trim()
    record(`${prefix}: autor contiene "Nicole"`, /nicole/i.test(authorTxt), `"${authorTxt}"`)
    record(`${prefix}: autor contiene "Ex Alumna"`, /ex\s*alumna/i.test(authorTxt), `"${authorTxt}"`)

    // Card radius desktop ≥ 80px (82px esperado).
    const cardRadius = await page.locator('.jlb-testimoniales__card').first().evaluate(el => {
        const cs = getComputedStyle(el)
        return {
            tl: cs.borderTopLeftRadius, tr: cs.borderTopRightRadius,
            br: cs.borderBottomRightRadius, bl: cs.borderBottomLeftRadius,
        }
    })
    const cardTL = parseFloat(cardRadius.tl) || 0
    record(
        `${prefix}: card border-radius ≥ 80px (desktop)`,
        cardTL >= 80,
        `tl=${cardRadius.tl}`,
    )

    // Media radius asimétrico (4 valores distintos: 134, 33, 52, 82).
    const mediaRadius = await page.locator('.jlb-testimoniales__media').first().evaluate(el => {
        const cs = getComputedStyle(el)
        return {
            tl: cs.borderTopLeftRadius, tr: cs.borderTopRightRadius,
            br: cs.borderBottomRightRadius, bl: cs.borderBottomLeftRadius,
        }
    })
    const distinctRadii = new Set([mediaRadius.tl, mediaRadius.tr, mediaRadius.br, mediaRadius.bl]).size
    record(
        `${prefix}: media border-radius asimétrico (4 valores distintos)`,
        distinctRadii === 4,
        `tl=${mediaRadius.tl} tr=${mediaRadius.tr} br=${mediaRadius.br} bl=${mediaRadius.bl}`,
    )

    const quoteSvg = await page.locator('.jlb-testimoniales__quote-mark svg').count()
    record(`${prefix}: SVG comillas en el DOM`, quoteSvg >= 1, `count=${quoteSvg}`)

    const playCount = await page.locator('.jlb-testimoniales__play').count()
    record(`${prefix}: SIN .jlb-testimoniales__play (sin video_url)`, playCount === 0, `count=${playCount}`)

    // Nav: con 1 slide PHP no la renderiza ($has_nav = total > 1).
    const navCount = await page.locator('.jlb-testimoniales__nav').count()
    record(`${prefix}: nav ausente con 1 solo slide`, navCount === 0, `count=${navCount}`)

    // Decoración visible desktop.
    const decorVisible = await page.locator('.jlb-testimoniales__decor').first().evaluate(el => {
        const cs = getComputedStyle(el)
        return cs.display !== 'none' && cs.visibility !== 'hidden'
    }).catch(() => false)
    record(`${prefix}: decoración curva visible (desktop)`, decorVisible)

    // Color cita = #2A2938 → rgb(42, 41, 56).
    const quoteColor = await page.locator('.jlb-testimoniales__quote p').first().evaluate(el => getComputedStyle(el).color)
    record(
        `${prefix}: cita color ≈ #2A2938`,
        quoteColor === 'rgb(42, 41, 56)',
        `computed="${quoteColor}"`,
    )

    // Fuentes.
    const kickerFF = await page.locator('.jlb-testimoniales__kicker').first().evaluate(el => getComputedStyle(el).fontFamily)
    const titleFF  = await page.locator('.jlb-testimoniales__title').first().evaluate(el => getComputedStyle(el).fontFamily)
    record(`${prefix}: kicker font-family menciona Raleway`, /raleway/i.test(kickerFF), `"${kickerFF}"`)
    record(`${prefix}: título font-family menciona KG Second Chances`, /kg second chances/i.test(titleFF), `"${titleFF}"`)

    const fontsLoaded = await page.evaluate(() => {
        if (!document.fonts) return { raleway: null, kg: null }
        return {
            raleway: document.fonts.check('400 15px "Raleway"'),
            kg: document.fonts.check('400 36px "KG Second Chances Solid"'),
        }
    })
    record(`${prefix}: Raleway 15px disponible en document.fonts`, fontsLoaded.raleway === true, `check=${fontsLoaded.raleway}`)
    record(`${prefix}: KG Second Chances 36px disponible en document.fonts`, fontsLoaded.kg === true, `check=${fontsLoaded.kg}`)
}

async function runMobileAsserts(page, prefix) {
    console.log(`\n→ ${prefix} — Asserts DOM (mobile 375):`)

    const decor = page.locator('.jlb-testimoniales__decor').first()
    const decorCount = await decor.count()
    if (decorCount === 0) {
        record(`${prefix}: decoración no renderizada en mobile (count=0)`, true)
    } else {
        const decorDisplay = await decor.evaluate(el => getComputedStyle(el).display)
        record(
            `${prefix}: decoración display:none en mobile <640`,
            decorDisplay === 'none',
            `display=${decorDisplay}`,
        )
    }

    const cardTL = await page.locator('.jlb-testimoniales__card').first().evaluate(el => parseFloat(getComputedStyle(el).borderTopLeftRadius))
    record(
        `${prefix}: card border-radius 32px en mobile <640`,
        Math.round(cardTL) === 32,
        `tl=${cardTL}px`,
    )

    const slideCols = await page.locator('.jlb-testimoniales__slide').first().evaluate(el => getComputedStyle(el).gridTemplateColumns)
    const cols = slideCols.split(' ').filter(Boolean)
    record(
        `${prefix}: slide grid 1 columna en mobile <640`,
        cols.length === 1,
        `"${slideCols}"`,
    )
}

// ── CASE 1: home (front-page.php) ─────────────────────────────────────────
async function caseSingle() {
    console.log('\n============================================================')
    console.log('CASO 1 — single (home /)')
    console.log('============================================================')

    for (const vp of VIEWPORTS) {
        const { browser, page } = await launchBrowser({ viewport: { width: vp.width, height: vp.height } })
        page.on('console', m => {
            if (m.type() === 'error') results.consoleErrors.push(`[case-single|${vp.name}] ${m.text()}`)
        })
        page.on('pageerror', e => results.pageErrors.push(`[case-single|${vp.name}] ${e.message}`))
        page.on('response', resp => {
            const url = resp.url()
            if (
                (url.includes('swiper') || url.includes('testimoniales') || url.includes('build/') || url.includes('figma-home'))
                && resp.status() >= 400
            ) {
                results.consoleErrors.push(`[case-single|${vp.name}] HTTP ${resp.status()} ${url}`)
            }
        })

        try {
            await page.goto(`${WP_SITE_URL}/`, { waitUntil: 'networkidle', timeout: 30000 })
            await waitForReady(page, `case-single|${vp.name}`)

            if (vp.name === 'desktop-1440') {
                await runSingleAsserts(page, 'case-single')
            }
            if (vp.name === 'mobile-375') {
                await runMobileAsserts(page, 'case-single')
            }

            const file = path.join(SCREENSHOTS_DIR, `jlb-testimoniales-${vp.name}.png`)
            const handle = await page.$('.jlb-testimoniales')
            if (handle) {
                await handle.scrollIntoViewIfNeeded()
                await page.waitForTimeout(400)
                await handle.screenshot({ path: file })
            } else {
                await page.screenshot({ path: file, fullPage: true })
            }
            results.screenshots.push(file)
            console.log(`📸 ${vp.name} → ${file}`)
        } catch (err) {
            console.error(`  ❌ case-single ${vp.name}:`, err.message)
        } finally {
            await browser.close()
        }
    }
}

// ── Helper: navega a la página recién creada de QA y verifica módulo ───────
async function createOrUpdateQaPage(page, items, mostrarPlay = false) {
    // Estrategia: ir a wp-admin → buscar página "QA Testimoniales".
    // Si existe, editarla. Si no, crear nueva.
    await page.goto(`${WP_SITE_URL}/wp-admin/edit.php?post_type=page&s=QA+Testimoniales`, { waitUntil: 'domcontentloaded' })
    await page.waitForSelector('table.wp-list-table', { timeout: 10000 })

    const exists = await page.locator('table.wp-list-table tbody tr a.row-title', { hasText: 'QA Testimoniales' }).count()

    if (exists > 0) {
        await page.locator('table.wp-list-table tbody tr a.row-title', { hasText: 'QA Testimoniales' }).first().click()
        await page.waitForLoadState('domcontentloaded')
    } else {
        // Crear nueva.
        await page.goto(`${WP_SITE_URL}/wp-admin/post-new.php?post_type=page`, { waitUntil: 'domcontentloaded' })
        await page.waitForTimeout(2000)

        // Dismiss welcome guide si aparece.
        try { await page.locator('button[aria-label="Cerrar"]').first().click({ timeout: 1500 }) } catch {}
        try { await page.locator('button[aria-label="Close"]').first().click({ timeout: 1500 }) } catch {}

        // Detectar editor: clásico vs Gutenberg.
        const isGutenberg = await page.locator('.edit-post-visual-editor, .editor-styles-wrapper, .block-editor').count() > 0

        if (isGutenberg) {
            // Set title.
            const titleEl = page.locator('.editor-post-title__input, [aria-label*="Añade título"], [aria-label*="Add title"], [placeholder*="Añade título"], [placeholder*="Add title"]').first()
            await titleEl.click()
            await titleEl.fill('QA Testimoniales')
            await page.waitForTimeout(500)
        } else {
            await page.locator('#title').fill('QA Testimoniales')
        }
    }

    // Esperar a que ACF esté presente.
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 })
    await page.waitForTimeout(2000)

    return await fillAcfTestimoniales(page, items, mostrarPlay)
}

async function fillAcfTestimoniales(page, items, mostrarPlay) {
    // 1) Verificar si ya hay un layout `jlb_testimoniales` agregado.
    const flex = page.locator('.acf-field-flexible-content').first()

    // Contar layouts visibles existentes (no clones).
    let existingLayouts = await flex.locator('.acf-flexible-content > .values > .layout[data-layout="jlb_testimoniales"]').count()
    // Considerar el wrapper <strong> documentado: usar descendiente.
    if (existingLayouts === 0) {
        existingLayouts = await flex.locator('.acf-flexible-content .values .layout[data-layout="jlb_testimoniales"]').count()
    }

    if (existingLayouts === 0) {
        // Click en el botón "Agregar fila" / "Add Row" del flexible.
        // ACF: <a class="acf-button button button-primary acf-icon-plus" data-event="add-layout">
        const addBtn = flex.locator('.acf-actions a.acf-button[data-event="add-layout"], .acf-actions a[data-name="add-layout"]').first()
        const addBtnCount = await addBtn.count()
        if (addBtnCount === 0) {
            results.blockers.push('No se encontró botón "Agregar fila" en el campo flexible ACF.')
            return false
        }
        await addBtn.click()
        await page.waitForTimeout(500)

        // Aparece popup con la lista de layouts. Buscar "JLB · Testimoniales".
        const popupItem = page.locator('.acf-fc-popup a[data-layout="jlb_testimoniales"], .tippy-box a[data-layout="jlb_testimoniales"]').first()
        const popupCount = await popupItem.count()
        if (popupCount === 0) {
            // Tomar screenshot del popup para diagnóstico.
            const debug = path.join(SCREENSHOTS_DIR, 'jlb-testimoniales-DEBUG-popup.png')
            await page.screenshot({ path: debug, fullPage: true })
            results.blockers.push(`Layout "jlb_testimoniales" NO aparece en el popup del Flexible Content. Screenshot: ${debug}`)
            return false
        }
        await popupItem.click()
        await page.waitForTimeout(1500)
    }

    // 2) Llenar el layout (el primero/único de tipo jlb_testimoniales).
    const layout = flex.locator('.acf-flexible-content .values .layout[data-layout="jlb_testimoniales"]').first()

    // Si está collapsed, expandir.
    const isCollapsed = await layout.evaluate(el => el.classList.contains('-collapsed')).catch(() => false)
    if (isCollapsed) {
        await layout.locator('[data-name="collapse-layout"]').first().click()
        await page.waitForTimeout(500)
    }

    // kicker.
    const kickerInput = layout.locator('[data-name="kicker"] input').first()
    await kickerInput.fill('')
    await kickerInput.type('Testimoniales')

    // mostrar_arco_decorativo (true_false): asegurar ON.
    const arcoCheckbox = layout.locator('[data-name="mostrar_arco_decorativo"] input[type="checkbox"]').first()
    const arcoChecked = await arcoCheckbox.isChecked().catch(() => false)
    if (!arcoChecked) {
        await arcoCheckbox.check()
    }

    // 3) Items (repeater). Limpiar lo existente y agregar los items pedidos.
    const repeater = layout.locator('[data-name="items"]').first()
    // Tomar las filas reales (no clones).
    const existingRows = await repeater.locator('.acf-repeater > table > tbody > .acf-row:not(.acf-clone)').count()
    const targetRows = items.length

    // Si hay más rows que items, no eliminamos (complejo). Si hay menos, agregamos.
    // Si igual, solo modificamos.
    if (existingRows < targetRows) {
        const addRowBtn = repeater.locator('.acf-actions a.acf-button[data-event="add-row"], .acf-actions a[data-name="add-row"]').first()
        for (let i = existingRows; i < targetRows; i++) {
            await addRowBtn.click()
            await page.waitForTimeout(600)
        }
    }

    // 4) Para cada ítem, llenar campos.
    for (let i = 0; i < items.length; i++) {
        const item = items[i]
        // Filas reales (excluir clone). nth(i).
        const row = repeater.locator('.acf-repeater > table > tbody > .acf-row:not(.acf-clone)').nth(i)

        // Imagen: usar Media Library, seleccionar primera con "nicole" en el nombre.
        if (item.image) {
            await fillAcfImage(page, row, item.image)
        }
        // video_url
        const videoInput = row.locator('[data-name="video_url"] input').first()
        await videoInput.fill('')
        if (item.video_url) await videoInput.type(item.video_url)
        // titulo
        const tInput = row.locator('[data-name="titulo"] input').first()
        await tInput.fill('')
        await tInput.type(item.titulo || '')
        // cita
        const cArea = row.locator('[data-name="cita"] textarea').first()
        await cArea.fill('')
        await cArea.type(item.cita || '')
        // autor_nombre
        const anInput = row.locator('[data-name="autor_nombre"] input').first()
        await anInput.fill('')
        await anInput.type(item.autor_nombre || '')
        // autor_rol
        const arInput = row.locator('[data-name="autor_rol"] input').first()
        await arInput.fill('')
        await arInput.type(item.autor_rol || '')
    }

    return true
}

async function fillAcfImage(page, row, imageQuery) {
    // Click "Add image" si está vacío, o "Edit" si ya tiene.
    const imgField = row.locator('[data-name="imagen"]').first()
    const addBtn   = imgField.locator('a.acf-button[data-name="add"], a[data-name="add"]').first()
    const editBtn  = imgField.locator('a.acf-button[data-name="edit"], a[data-name="edit"]').first()

    const hasImg = await imgField.locator('img').count() > 0
    if (hasImg) {
        // Ya tiene imagen; asumir OK.
        return
    }
    await addBtn.click()
    // Media library modal abre. Esperar.
    await page.waitForSelector('.media-modal', { timeout: 10000 })
    await page.waitForTimeout(1500)

    // Click tab "Media Library" si está en "Upload Files".
    const tabLib = page.locator('.media-modal .media-router a').filter({ hasText: /Media Library|Biblioteca/i }).first()
    if (await tabLib.count() > 0) {
        await tabLib.click()
        await page.waitForTimeout(800)
    }

    // Buscar por nombre.
    const searchInput = page.locator('.media-modal #media-search-input, .media-modal input[type="search"]').first()
    await searchInput.fill(imageQuery)
    await page.waitForTimeout(1500)

    // Click primer attachment.
    const firstAttachment = page.locator('.media-modal .attachments .attachment').first()
    const found = await firstAttachment.count()
    if (found === 0) {
        // No hay archivo en media. Upload del filesystem.
        const tabUp = page.locator('.media-modal .media-router a').filter({ hasText: /Upload|Subir/i }).first()
        await tabUp.click()
        await page.waitForTimeout(600)
        const fileInput = page.locator('.media-modal input[type="file"]').first()
        const localPath = path.join(SCR_DIR, '..', 'assets', 'figma-home', 'testimonial-nicole.png')
        await fileInput.setInputFiles(localPath)
        await page.waitForTimeout(3000)
        // Vuelve a tab library tras upload.
        await page.waitForSelector('.media-modal .attachments .attachment.selected', { timeout: 15000 })
    } else {
        await firstAttachment.click()
        await page.waitForTimeout(500)
    }

    // Click "Select" / "Seleccionar".
    const selectBtn = page.locator('.media-modal .media-toolbar .media-button-select, .media-modal .media-toolbar button').filter({ hasText: /Select|Seleccionar|Insertar|Use|Establecer/i }).first()
    await selectBtn.click()
    await page.waitForTimeout(1500)
}

async function publishAndGetSlug(page) {
    // Detectar editor.
    const isGutenberg = await page.locator('.edit-post-visual-editor, .editor-styles-wrapper, .block-editor').count() > 0

    if (isGutenberg) {
        // Botón "Publicar" en top bar Gutenberg.
        const pubTop = page.locator('button.editor-post-publish-button__button, button.editor-post-publish-panel__toggle').first()
        await pubTop.click()
        await page.waitForTimeout(800)
        // Confirmar en panel
        const pubConfirm = page.locator('button.editor-post-publish-button').first()
        if (await pubConfirm.count() > 0) {
            await pubConfirm.click()
            await page.waitForTimeout(2000)
        }
    } else {
        const publishBtn = page.locator('#publish').first()
        await publishBtn.click()
        await page.waitForTimeout(3000)
    }

    // Obtener slug/url desde "View Page" link.
    await page.waitForTimeout(2000)
    const viewLink = await page.locator('a.components-button.is-link, a.post-edit-link, #sample-permalink a, .editor-post-publish-panel__postpublish-buttons a').filter({ hasText: /Ver página|View page|Ver|View/i }).first().getAttribute('href').catch(() => null)
    if (viewLink) return viewLink

    // Fallback: leer el slug del input #post_name.
    const slug = await page.locator('#post_name, [name="post_name"]').first().inputValue().catch(() => null)
    if (slug) return `${WP_SITE_URL}/${slug}/`

    return `${WP_SITE_URL}/?p=`
}

// ── CASE 2 + 3 (multi y con video) usando la misma página ─────────────────
async function casesMultiAndPlay() {
    console.log('\n============================================================')
    console.log('CASO 2 — multi (≥ 2 slides, sin video) + CASO 3 — con video')
    console.log('============================================================')

    const adminSession = await launchAdminSession({ viewport: { width: 1440, height: 900 } })
    const page = adminSession.page

    page.on('console', m => {
        if (m.type() === 'error') results.consoleErrors.push(`[admin] ${m.text()}`)
    })
    page.on('pageerror', e => results.pageErrors.push(`[admin] ${e.message}`))

    // Paso A: poblar con 2 ítems sin video → caso multi.
    const itemsMulti = [
        {
            image: 'nicole',
            video_url: '',
            titulo: 'Me motivaron a conseguir mis objetivos',
            cita: 'En Jean Le Boulch las herramientas base que me dieron me ayudaron a seguir un crecimiento profesional y proyectarme solidamente.',
            autor_nombre: 'Nicole',
            autor_rol: 'Ex Alumna',
        },
        {
            image: 'nicole',
            video_url: '',
            titulo: 'Encontré mi vocación aquí',
            cita: 'Los profesores nos guiaron a explorar nuestras inquietudes y descubrir lo que nos apasiona, y eso marcó mi futuro profesional.',
            autor_nombre: 'Carla',
            autor_rol: 'Ex Alumna',
        },
    ]

    let ok = await createOrUpdateQaPage(page, itemsMulti, false)
    if (!ok) {
        await adminSession.browser.close()
        return null
    }

    const url1 = await publishAndGetSlug(page)
    console.log(`  publicado (multi sin video): ${url1}`)

    // Screenshot frontend MULTI.
    const fileMulti = path.join(SCREENSHOTS_DIR, 'jlb-testimoniales-desktop-multi.png')
    await page.goto(url1, { waitUntil: 'networkidle', timeout: 30000 })
    try {
        await waitForReady(page, 'case-multi')
    } catch {
        results.blockers.push(`No se renderiza el módulo en la página publicada: ${url1}`)
        await adminSession.browser.close()
        return null
    }
    {
        console.log('\n→ case-multi — Asserts DOM:')
        const slides = await page.locator('.jlb-testimoniales__slide').count()
        record('case-multi: 2 slides', slides === 2, `count=${slides}`)

        const nav = page.locator('.jlb-testimoniales__nav').first()
        const navCount = await nav.count()
        record('case-multi: .jlb-testimoniales__nav renderizado', navCount === 1, `count=${navCount}`)

        if (navCount === 1) {
            const navHidden = await nav.evaluate(el => el.classList.contains('is-hidden') || getComputedStyle(el).display === 'none')
            record('case-multi: nav NO oculto', !navHidden, `is-hidden/display:none = ${navHidden}`)
        }

        // aria-label "Testimonio 2 de 2" en segunda slide.
        const aria2 = await page.locator('.jlb-testimoniales__slide').nth(1).getAttribute('aria-label')
        record('case-multi: aria-label slide 2 = "Testimonio 2 de 2"', /testimonio 2 de 2/i.test(aria2 || ''), `aria="${aria2}"`)

        const handle = await page.$('.jlb-testimoniales')
        await handle.scrollIntoViewIfNeeded()
        await page.waitForTimeout(400)
        await handle.screenshot({ path: fileMulti })
        results.screenshots.push(fileMulti)
        console.log(`📸 case-multi → ${fileMulti}`)

        // Click next y screenshot post-click.
        const nextBtn = page.locator('.jlb-testimoniales__next').first()
        if (await nextBtn.count() > 0) {
            await nextBtn.click()
            await page.waitForTimeout(1000)
            const fileMultiNext = path.join(SCREENSHOTS_DIR, 'jlb-testimoniales-desktop-multi-next.png')
            await handle.screenshot({ path: fileMultiNext })
            results.screenshots.push(fileMultiNext)
            console.log(`📸 case-multi-next → ${fileMultiNext}`)
        }
    }

    // Paso B: editar misma página, agregar video_url al primer ítem → caso play.
    await page.goto(`${WP_SITE_URL}/wp-admin/edit.php?post_type=page&s=QA+Testimoniales`, { waitUntil: 'domcontentloaded' })
    await page.waitForSelector('table.wp-list-table', { timeout: 10000 })
    await page.locator('table.wp-list-table tbody tr a.row-title', { hasText: 'QA Testimoniales' }).first().click()
    await page.waitForLoadState('domcontentloaded')
    await page.waitForSelector('.acf-field-flexible-content', { timeout: 20000 })
    await page.waitForTimeout(2000)

    // Set video_url en primer item.
    const flex = page.locator('.acf-field-flexible-content').first()
    const layout = flex.locator('.acf-flexible-content .values .layout[data-layout="jlb_testimoniales"]').first()

    const isCollapsed = await layout.evaluate(el => el.classList.contains('-collapsed')).catch(() => false)
    if (isCollapsed) {
        await layout.locator('[data-name="collapse-layout"]').first().click()
        await page.waitForTimeout(500)
    }
    const repeater = layout.locator('[data-name="items"]').first()
    const firstRow = repeater.locator('.acf-repeater > table > tbody > .acf-row:not(.acf-clone)').first()
    const videoInput = firstRow.locator('[data-name="video_url"] input').first()
    await videoInput.fill('')
    await videoInput.type('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    await page.waitForTimeout(400)

    // Update.
    const url2 = await publishAndGetSlug(page)
    console.log(`  actualizado (con video): ${url2 || url1}`)
    const targetUrl = url2 || url1

    // Screenshot frontend WITH PLAY.
    await page.goto(targetUrl, { waitUntil: 'networkidle', timeout: 30000 })
    try {
        await waitForReady(page, 'case-play')
    } catch {
        results.blockers.push(`No se renderiza módulo en case-play: ${targetUrl}`)
        await adminSession.browser.close()
        return null
    }
    {
        console.log('\n→ case-play — Asserts DOM:')
        const playCount = await page.locator('.jlb-testimoniales__play').count()
        record('case-play: .jlb-testimoniales__play visible (con video)', playCount >= 1, `count=${playCount}`)

        if (playCount >= 1) {
            const playHref = await page.locator('.jlb-testimoniales__play').first().getAttribute('href')
            record('case-play: href del play apunta al YouTube', /youtube\.com/.test(playHref || ''), `href="${playHref}"`)
            const playVisible = await page.locator('.jlb-testimoniales__play').first().evaluate(el => {
                const cs = getComputedStyle(el)
                return cs.display !== 'none' && cs.visibility !== 'hidden'
            })
            record('case-play: play NO oculto (computed)', playVisible)
        }

        const fileWithPlay = path.join(SCREENSHOTS_DIR, 'jlb-testimoniales-desktop-withplay.png')
        const handle = await page.$('.jlb-testimoniales')
        await handle.scrollIntoViewIfNeeded()
        await page.waitForTimeout(400)
        await handle.screenshot({ path: fileWithPlay })
        results.screenshots.push(fileWithPlay)
        console.log(`📸 case-play → ${fileWithPlay}`)
    }

    await adminSession.browser.close()
}

// ── Run ────────────────────────────────────────────────────────────────────
console.log('============================================================')
console.log('QA Visual — jlb-testimoniales')
console.log(`Site: ${WP_SITE_URL}`)
console.log(`Output dir: ${SCREENSHOTS_DIR}`)
console.log('============================================================')

await caseSingle()
await casesMultiAndPlay().catch(err => {
    console.error('❌ casesMultiAndPlay error:', err.message)
    results.blockers.push(`Excepción en casesMultiAndPlay: ${err.message}`)
})

// ── Resumen ────────────────────────────────────────────────────────────────
console.log('\n============================================================')
console.log('RESUMEN')
console.log('============================================================')
const passed = results.asserts.filter(a => a.ok).length
const total  = results.asserts.length
console.log(`Asserts: ${passed}/${total} OK`)
console.log(`Screenshots: ${results.screenshots.length}`)
console.log(`Console errors: ${results.consoleErrors.length}`)
console.log(`Page errors: ${results.pageErrors.length}`)
console.log(`Bloqueantes: ${results.blockers.length}`)

if (results.blockers.length) {
    console.log('\n--- BLOQUEANTES ---')
    results.blockers.forEach(b => console.log(' ▸', b))
}
if (results.consoleErrors.length) {
    console.log('\n--- CONSOLE ERRORS ---')
    results.consoleErrors.forEach(e => console.log(' •', e))
}
if (results.pageErrors.length) {
    console.log('\n--- PAGE ERRORS ---')
    results.pageErrors.forEach(e => console.log(' •', e))
}
if (results.notes.length) {
    console.log('\n--- NOTES ---')
    results.notes.forEach(n => console.log(' •', n))
}

const ok = passed === total && results.pageErrors.length === 0 && results.blockers.length === 0
console.log(ok ? '\n✅ PASS' : '\n❌ FAIL (revisa lo anterior)')

// Dump JSON para el reporte.
const report = path.join(SCR_DIR, 'jlb-testimoniales-report.json')
fs.writeFileSync(report, JSON.stringify(results, null, 2))
console.log(`\nReporte JSON: ${report}`)

process.exitCode = ok ? 0 : 1
