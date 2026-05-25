/**
 * QA visual frontend-only — módulo jlb-testimoniales.
 *
 * NO entra a wp-admin. Solo navega 3 URLs públicas ya pobladas por seeder:
 *   - single     → /qa-testimoniales-·-single/    (1 ítem, sin video, sin nav)
 *   - multi      → /qa-testimoniales-·-multi/     (3 ítems Nicole/Carla/Diego, nav activo)
 *   - con-video  → /qa-testimoniales-·-con-video/ (1 ítem con video_url → play visible)
 *
 * Screenshots: qa/screenshots/jlb-testimoniales-<caso>-<viewport>.png
 *
 * Reporte JSON: qa/jlb-testimoniales-report-v2.json
 */
import path from 'node:path'
import fs from 'node:fs'
import { launchBrowser, SCR_DIR, WP_SITE_URL } from './lib/wp-playwright.mjs'

const SCREENSHOTS_DIR = path.join(SCR_DIR, 'screenshots')
if (!fs.existsSync(SCREENSHOTS_DIR)) fs.mkdirSync(SCREENSHOTS_DIR, { recursive: true })

const CASES = [
    {
        name: 'single',
        url: `${WP_SITE_URL}/qa-testimoniales-%c2%b7-single/`,
        fallback: `${WP_SITE_URL}/?p=97`,
    },
    {
        name: 'multi',
        url: `${WP_SITE_URL}/qa-testimoniales-%c2%b7-multi/`,
        fallback: `${WP_SITE_URL}/?p=98`,
    },
    {
        name: 'con-video',
        url: `${WP_SITE_URL}/qa-testimoniales-%c2%b7-con-video/`,
        fallback: `${WP_SITE_URL}/?p=99`,
    },
]

const VIEWPORTS = [
    { name: '1440', width: 1440, height: 900 },
    { name: '980',  width: 980,  height: 768 },
    { name: '640',  width: 640,  height: 900 },
    { name: '375',  width: 375,  height: 800 },
]

const results = {
    screenshots: [],
    asserts: [],
    consoleErrors: [],
    pageErrors: [],
    httpErrors: [],
    notes: [],
    figma: [],
}

function record(name, ok, detail = '') {
    results.asserts.push({ name, ok, detail })
    console.log(`  ${ok ? '✓' : '✗'} ${name}${detail ? ` — ${detail}` : ''}`)
}

async function waitForReady(page, label) {
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
    await page.waitForTimeout(900)
}

async function navigateWithFallback(page, caso) {
    let response = await page.goto(caso.url, { waitUntil: 'networkidle', timeout: 30000 }).catch(() => null)
    if (!response || response.status() >= 400) {
        results.notes.push(`[${caso.name}] URL principal falló (${response?.status?.() ?? 'no response'}). Probando fallback ${caso.fallback}.`)
        response = await page.goto(caso.fallback, { waitUntil: 'networkidle', timeout: 30000 }).catch(() => null)
    }
    return response
}

async function captureScreenshots(caso) {
    for (const vp of VIEWPORTS) {
        const { browser, page } = await launchBrowser({ viewport: { width: vp.width, height: vp.height } })

        // hook errores
        page.on('console', m => {
            if (m.type() === 'error') results.consoleErrors.push(`[${caso.name}|${vp.name}] ${m.text()}`)
        })
        page.on('pageerror', e => results.pageErrors.push(`[${caso.name}|${vp.name}] ${e.message}`))
        page.on('response', resp => {
            const url = resp.url()
            const status = resp.status()
            if (status >= 400 && !url.endsWith('favicon.ico')) {
                results.httpErrors.push(`[${caso.name}|${vp.name}] HTTP ${status} ${url}`)
            }
        })

        try {
            const resp = await navigateWithFallback(page, caso)
            if (!resp) {
                console.log(`  ❌ no se pudo cargar ${caso.url}`)
                continue
            }
            try {
                await waitForReady(page, `${caso.name}|${vp.name}`)
            } catch (e) {
                console.log(`  ⚠️ ${caso.name}|${vp.name}: ${e.message}`)
            }

            const file = path.join(SCREENSHOTS_DIR, `jlb-testimoniales-${caso.name}-${vp.name}.png`)
            // Para extremos (375 y 1440) screenshot fullPage; para intermedios sólo módulo.
            if (vp.name === '1440' || vp.name === '375') {
                await page.screenshot({ path: file, fullPage: true })
            } else {
                const handle = await page.$('.jlb-testimoniales')
                if (handle) {
                    await handle.scrollIntoViewIfNeeded()
                    await page.waitForTimeout(300)
                    await handle.screenshot({ path: file })
                } else {
                    await page.screenshot({ path: file, fullPage: true })
                }
            }
            results.screenshots.push(file)
            console.log(`  📸 ${vp.name} → ${path.relative(SCR_DIR, file)}`)
        } catch (err) {
            console.error(`  ❌ ${caso.name}|${vp.name}:`, err.message)
        } finally {
            await browser.close()
        }
    }
}

// ── Asserts DOM caso multi (3 slides, nav activo) ─────────────────────────
async function assertsMulti() {
    console.log('\n→ Asserts DOM — caso multi @ 1440:')
    const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } })
    page.on('console', m => {
        if (m.type() === 'error') results.consoleErrors.push(`[multi-asserts] ${m.text()}`)
    })
    page.on('pageerror', e => results.pageErrors.push(`[multi-asserts] ${e.message}`))

    try {
        await navigateWithFallback(page, CASES[1])
        await waitForReady(page, 'multi-asserts')

        const sectionCount = await page.locator('section.jlb-testimoniales').count()
        record('multi: <section class="jlb-testimoniales"> existe', sectionCount >= 1, `count=${sectionCount}`)

        const slideCount = await page.locator('.jlb-testimoniales .swiper-slide').count()
        record('multi: 3 .swiper-slide', slideCount === 3, `count=${slideCount}`)

        const kicker = (await page.locator('.jlb-testimoniales__kicker').first().textContent() || '').trim()
        record('multi: kicker contiene "Testimoniales"', /testimoniales/i.test(kicker), `"${kicker}"`)

        const slide0Title = (await page.locator('.jlb-testimoniales .swiper-slide').nth(0).locator('.jlb-testimoniales__title').textContent() || '').trim()
        record('multi: slide 1 título = "Me motivaron a conseguir mis objetivos"',
            /me motivaron a conseguir mis objetivos/i.test(slide0Title),
            `"${slide0Title}"`)

        const slide0Author = (await page.locator('.jlb-testimoniales .swiper-slide').nth(0).locator('.jlb-testimoniales__author').textContent() || '').trim()
        record('multi: slide 1 autor incluye "Nicole"', /nicole/i.test(slide0Author), `"${slide0Author}"`)
        record('multi: slide 1 autor incluye "Ex Alumna"', /ex\s*alumna/i.test(slide0Author), `"${slide0Author}"`)

        const slide1Author = (await page.locator('.jlb-testimoniales .swiper-slide').nth(1).locator('.jlb-testimoniales__author').textContent() || '').trim()
        record('multi: slide 2 autor incluye "Carla"', /carla/i.test(slide1Author), `"${slide1Author}"`)

        const slide2Author = (await page.locator('.jlb-testimoniales .swiper-slide').nth(2).locator('.jlb-testimoniales__author').textContent() || '').trim()
        record('multi: slide 3 autor incluye "Diego"', /diego/i.test(slide2Author), `"${slide2Author}"`)

        // Card radius
        const card = page.locator('.jlb-testimoniales__card').first()
        const cardCount = await card.count()
        if (cardCount > 0) {
            const cardRadius = await card.evaluate(el => {
                const cs = getComputedStyle(el)
                return {
                    tl: cs.borderTopLeftRadius, tr: cs.borderTopRightRadius,
                    br: cs.borderBottomRightRadius, bl: cs.borderBottomLeftRadius,
                }
            })
            const cardTL = parseFloat(cardRadius.tl) || 0
            record('multi: card border-radius ≥ 80px (desktop 1440)', cardTL >= 80, `tl=${cardRadius.tl}`)
        } else {
            record('multi: card border-radius ≥ 80px (desktop 1440)', false, 'no se encontró .jlb-testimoniales__card')
        }

        // Media radius asimétrico ~ tl=134, tr=33, br=52, bl=82
        const media = page.locator('.jlb-testimoniales__media').first()
        if (await media.count() > 0) {
            const mediaRadius = await media.evaluate(el => {
                const cs = getComputedStyle(el)
                return {
                    tl: cs.borderTopLeftRadius, tr: cs.borderTopRightRadius,
                    br: cs.borderBottomRightRadius, bl: cs.borderBottomLeftRadius,
                }
            })
            const distinctRadii = new Set([mediaRadius.tl, mediaRadius.tr, mediaRadius.br, mediaRadius.bl]).size
            record('multi: media border-radius asimétrico (4 valores distintos)',
                distinctRadii === 4,
                `tl=${mediaRadius.tl} tr=${mediaRadius.tr} br=${mediaRadius.br} bl=${mediaRadius.bl}`)
            const tl = parseFloat(mediaRadius.tl)
            const tr = parseFloat(mediaRadius.tr)
            const br = parseFloat(mediaRadius.br)
            const bl = parseFloat(mediaRadius.bl)
            record('multi: media radius dentro de tolerancia (tl≈134, tr≈33, br≈52, bl≈82 ±10)',
                Math.abs(tl - 134) <= 10 && Math.abs(tr - 33) <= 10 && Math.abs(br - 52) <= 10 && Math.abs(bl - 82) <= 10,
                `tl=${tl} tr=${tr} br=${br} bl=${bl}`)
        } else {
            record('multi: .jlb-testimoniales__media existe', false)
        }

        // Nav visible
        const nav = page.locator('.jlb-testimoniales__nav').first()
        const navCount = await nav.count()
        if (navCount > 0) {
            const navVisible = await nav.evaluate(el => {
                const cs = getComputedStyle(el)
                return cs.display !== 'none' && cs.visibility !== 'hidden' && !el.classList.contains('is-hidden')
            })
            record('multi: .jlb-testimoniales__nav visible', navVisible)
        } else {
            record('multi: .jlb-testimoniales__nav existe', false, 'count=0')
        }

        // Click next cambia slide activo
        const activeBefore = await page.locator('.jlb-testimoniales .swiper-slide-active').first().getAttribute('aria-label').catch(() => null)
        const altActiveBefore = activeBefore || await page.evaluate(() => {
            const a = document.querySelector('.jlb-testimoniales .swiper-slide-active')
            return a ? (a.dataset.swiperSlideIndex ?? a.innerText?.slice(0, 60) ?? 'unknown') : 'none'
        })
        const nextBtn = page.locator('.jlb-testimoniales__next').first()
        if (await nextBtn.count() > 0) {
            await nextBtn.click()
            await page.waitForTimeout(900)
            const activeAfter = await page.locator('.jlb-testimoniales .swiper-slide-active').first().getAttribute('aria-label').catch(() => null)
            const altActiveAfter = activeAfter || await page.evaluate(() => {
                const a = document.querySelector('.jlb-testimoniales .swiper-slide-active')
                return a ? (a.dataset.swiperSlideIndex ?? a.innerText?.slice(0, 60) ?? 'unknown') : 'none'
            })
            const changed = String(altActiveBefore) !== String(altActiveAfter)
            record('multi: click .jlb-testimoniales__next cambia slide activo',
                changed, `before="${altActiveBefore}" after="${altActiveAfter}"`)
        } else {
            record('multi: existe .jlb-testimoniales__next', false)
        }

        // No debería existir el play en multi (no tienen video_url)
        const playCount = await page.locator('.jlb-testimoniales__play, .jlb-testimoniales .swiper-slide a[href*="youtube"], .jlb-testimoniales .swiper-slide a[href*="youtu.be"]').count()
        record('multi: ningún botón play (slides sin video_url)', playCount === 0, `count=${playCount}`)

        // Tipografía / colores / fuentes
        const kickerFF = await page.locator('.jlb-testimoniales__kicker').first().evaluate(el => getComputedStyle(el).fontFamily)
        const titleFF  = await page.locator('.jlb-testimoniales__title').first().evaluate(el => getComputedStyle(el).fontFamily)
        record('multi: kicker font-family menciona Raleway', /raleway/i.test(kickerFF), `"${kickerFF}"`)
        record('multi: título font-family menciona KG Second Chances', /kg second chances/i.test(titleFF), `"${titleFF}"`)

        // El blockquote.jlb-testimoniales__quote envuelve un <span.__quote-mark> con SVG y un <p>
        // con el texto realmente formateado. Apuntamos al <p>.
        const quoteEl = page.locator('.jlb-testimoniales__quote > p').first()
        if (await quoteEl.count() > 0) {
            const quoteData = await quoteEl.evaluate(el => {
                const cs = getComputedStyle(el)
                return { color: cs.color, fontFamily: cs.fontFamily, fontStyle: cs.fontStyle, fontWeight: cs.fontWeight }
            })
            record('multi: cita color ≈ #2A2938 (rgb(42, 41, 56))',
                quoteData.color === 'rgb(42, 41, 56)',
                `computed="${quoteData.color}"`)
            record('multi: cita font-family menciona Raleway', /raleway/i.test(quoteData.fontFamily), `"${quoteData.fontFamily}"`)
            record('multi: cita fontStyle italic', quoteData.fontStyle === 'italic', `fontStyle="${quoteData.fontStyle}"`)
            record('multi: cita peso ligero (≤ 300)',
                parseInt(quoteData.fontWeight, 10) <= 300,
                `weight=${quoteData.fontWeight}`)
        }

        const fontsLoaded = await page.evaluate(() => {
            if (!document.fonts) return { raleway: null, kg: null, ralewayItalicLight: null }
            return {
                raleway: document.fonts.check('400 15px "Raleway"'),
                ralewayItalicLight: document.fonts.check('300 italic 18px "Raleway"'),
                kg: document.fonts.check('400 36px "KG Second Chances Solid"'),
            }
        })
        record('multi: Raleway 400 15px disponible (document.fonts.check)', fontsLoaded.raleway === true, `check=${fontsLoaded.raleway}`)
        record('multi: Raleway 300 italic 18px disponible (document.fonts.check)', fontsLoaded.ralewayItalicLight === true, `check=${fontsLoaded.ralewayItalicLight}`)
        record('multi: KG Second Chances 36px disponible (document.fonts.check)', fontsLoaded.kg === true, `check=${fontsLoaded.kg}`)

        if (fontsLoaded.raleway !== true || fontsLoaded.kg !== true || fontsLoaded.ralewayItalicLight !== true) {
            results.figma.push({ severity: 'BLOQUEANTE', detail: `Fuentes no cargan: Raleway=${fontsLoaded.raleway}, Raleway300Italic=${fontsLoaded.ralewayItalicLight}, KG=${fontsLoaded.kg}` })
        }

        // Arco decorativo visible en desktop
        const decor = page.locator('.jlb-testimoniales__decor, .jlb-testimoniales__arc, .jlb-testimoniales [class*="arc-decor"]').first()
        if (await decor.count() > 0) {
            const decorVisible = await decor.evaluate(el => {
                const cs = getComputedStyle(el)
                return cs.display !== 'none' && cs.visibility !== 'hidden'
            })
            record('multi: arco decorativo visible en desktop 1440', decorVisible)
        } else {
            record('multi: arco decorativo presente en DOM (desktop)', false, 'no .jlb-testimoniales__decor encontrado')
            results.figma.push({ severity: 'IMPORTANTE', detail: 'No se encontró .jlb-testimoniales__decor / arco decorativo en DOM en viewport 1440' })
        }

        // Pesos del autor (Nicole bold + " - " + "Ex Alumna" regular)
        const authorChildren = await page.locator('.jlb-testimoniales .swiper-slide').nth(0).locator('.jlb-testimoniales__author *').evaluateAll(els =>
            els.map(e => ({ tag: e.tagName, text: e.textContent.trim(), weight: getComputedStyle(e).fontWeight }))
        )
        if (authorChildren.length >= 2) {
            const weights = authorChildren.map(c => parseInt(c.weight, 10))
            const distinctWeights = new Set(weights).size
            record('multi: autor tiene ≥ 2 pesos tipográficos distintos (bold/regular)',
                distinctWeights >= 2,
                `weights=${weights.join(',')} children=${JSON.stringify(authorChildren.map(c => ({ t: c.text, w: c.weight })))}`)
        } else {
            record('multi: autor combina pesos (bold/medium/regular)', false, `children encontrados=${authorChildren.length}`)
            results.figma.push({ severity: 'MEDIO', detail: 'Autor no parece tener spans con pesos diferenciados; difícil verificar bold + medium + regular' })
        }
    } catch (err) {
        results.notes.push(`Error en assertsMulti: ${err.message}`)
        console.error('  ❌', err.message)
    } finally {
        await browser.close()
    }
}

// ── Asserts caso single (sin nav, sin play) ───────────────────────────────
async function assertsSingle() {
    console.log('\n→ Asserts DOM — caso single @ 1440:')
    const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } })
    page.on('console', m => {
        if (m.type() === 'error') results.consoleErrors.push(`[single-asserts] ${m.text()}`)
    })
    page.on('pageerror', e => results.pageErrors.push(`[single-asserts] ${e.message}`))

    try {
        await navigateWithFallback(page, CASES[0])
        await waitForReady(page, 'single-asserts')

        const slideCount = await page.locator('.jlb-testimoniales .swiper-slide').count()
        record('single: solo 1 slide (puede haber 0 si no usa swiper con 1)', slideCount <= 1 || slideCount === 1, `count=${slideCount}`)

        // Nav debería estar oculto/no renderizado
        const nav = page.locator('.jlb-testimoniales__nav')
        const navCount = await nav.count()
        if (navCount === 0) {
            record('single: .jlb-testimoniales__nav no presente o oculto', true, 'count=0')
        } else {
            const navHidden = await nav.first().evaluate(el => {
                const cs = getComputedStyle(el)
                return cs.display === 'none' || cs.visibility === 'hidden' || el.classList.contains('is-hidden')
            })
            record('single: .jlb-testimoniales__nav oculto (display:none / is-hidden)',
                navHidden,
                `count=${navCount}, hidden=${navHidden}`)
        }

        // No play button
        const playCount = await page.locator('.jlb-testimoniales__play, .jlb-testimoniales .swiper-slide a[href*="youtube"], .jlb-testimoniales .swiper-slide a[href*="youtu.be"]').count()
        record('single: ningún botón play (sin video_url)', playCount === 0, `count=${playCount}`)
    } catch (err) {
        results.notes.push(`Error en assertsSingle: ${err.message}`)
    } finally {
        await browser.close()
    }
}

// ── Asserts caso con-video (play button visible) ──────────────────────────
async function assertsConVideo() {
    console.log('\n→ Asserts DOM — caso con-video @ 1440:')
    const { browser, page } = await launchBrowser({ viewport: { width: 1440, height: 900 } })
    page.on('console', m => {
        if (m.type() === 'error') results.consoleErrors.push(`[video-asserts] ${m.text()}`)
    })
    page.on('pageerror', e => results.pageErrors.push(`[video-asserts] ${e.message}`))

    try {
        await navigateWithFallback(page, CASES[2])
        await waitForReady(page, 'video-asserts')

        const playSelector = '.jlb-testimoniales__play, .jlb-testimoniales .swiper-slide a[href*="youtube"], .jlb-testimoniales .swiper-slide a[href*="youtu.be"], .jlb-testimoniales__media a svg, .jlb-testimoniales__media button svg'
        const playCount = await page.locator(playSelector).count()
        record('con-video: botón play / link con SVG en media', playCount >= 1, `count=${playCount}`)

        if (playCount >= 1) {
            const playEl = page.locator(playSelector).first()
            const playVisible = await playEl.evaluate(el => {
                const cs = getComputedStyle(el)
                return cs.display !== 'none' && cs.visibility !== 'hidden'
            })
            record('con-video: play visible (computed)', playVisible)

            const href = await playEl.evaluate(el => el.closest('a')?.getAttribute('href') || el.getAttribute('href') || null)
            record('con-video: href apunta a YouTube', /youtube\.com|youtu\.be/.test(href || ''), `href="${href}"`)
        }
    } catch (err) {
        results.notes.push(`Error en assertsConVideo: ${err.message}`)
    } finally {
        await browser.close()
    }
}

// ── Asserts mobile 640: decor oculto ──────────────────────────────────────
async function assertsMobile() {
    console.log('\n→ Asserts DOM — caso multi @ 640 (mobile):')
    const { browser, page } = await launchBrowser({ viewport: { width: 640, height: 900 } })
    try {
        await navigateWithFallback(page, CASES[1])
        await waitForReady(page, 'mobile-asserts')

        const decor = page.locator('.jlb-testimoniales__decor, .jlb-testimoniales__arc, .jlb-testimoniales [class*="arc-decor"]').first()
        const decorCount = await decor.count()
        if (decorCount === 0) {
            record('mobile-640: arco decorativo no renderizado (count=0)', true)
        } else {
            const decorDisplay = await decor.evaluate(el => getComputedStyle(el).display)
            record('mobile-640: arco decorativo display:none', decorDisplay === 'none', `display=${decorDisplay}`)
        }
    } catch (err) {
        results.notes.push(`Error en assertsMobile: ${err.message}`)
    } finally {
        await browser.close()
    }
}

// ── Run ───────────────────────────────────────────────────────────────────
console.log('============================================================')
console.log('QA Visual — jlb-testimoniales (frontend-only)')
console.log(`Site: ${WP_SITE_URL}`)
console.log(`Output dir: ${SCREENSHOTS_DIR}`)
console.log('============================================================')

for (const caso of CASES) {
    console.log(`\n=== Caso: ${caso.name} (${caso.url}) ===`)
    await captureScreenshots(caso)
}

await assertsSingle()
await assertsMulti()
await assertsConVideo()
await assertsMobile()

// ── Resumen ───────────────────────────────────────────────────────────────
console.log('\n============================================================')
console.log('RESUMEN')
console.log('============================================================')
const passed = results.asserts.filter(a => a.ok).length
const total  = results.asserts.length
console.log(`Asserts: ${passed}/${total} OK`)
console.log(`Screenshots: ${results.screenshots.length}`)
console.log(`Console errors: ${results.consoleErrors.length}`)
console.log(`Page errors: ${results.pageErrors.length}`)
console.log(`HTTP errors: ${results.httpErrors.length}`)
console.log(`Figma observations: ${results.figma.length}`)

if (results.consoleErrors.length) {
    console.log('\n--- CONSOLE ERRORS ---')
    results.consoleErrors.forEach(e => console.log(' •', e))
}
if (results.pageErrors.length) {
    console.log('\n--- PAGE ERRORS ---')
    results.pageErrors.forEach(e => console.log(' •', e))
}
if (results.httpErrors.length) {
    console.log('\n--- HTTP ERRORS ---')
    results.httpErrors.forEach(e => console.log(' •', e))
}
if (results.figma.length) {
    console.log('\n--- FIGMA OBSERVATIONS ---')
    results.figma.forEach(f => console.log(` • [${f.severity}] ${f.detail}`))
}
if (results.notes.length) {
    console.log('\n--- NOTES ---')
    results.notes.forEach(n => console.log(' •', n))
}

const failedAsserts = results.asserts.filter(a => !a.ok)
if (failedAsserts.length) {
    console.log('\n--- FAILED ASSERTS ---')
    failedAsserts.forEach(a => console.log(` ✗ ${a.name} — ${a.detail}`))
}

const ok = passed === total && results.pageErrors.length === 0
console.log(ok ? '\n✅ PASS' : '\n⚠️ Revisar fallos')

const report = path.join(SCR_DIR, 'jlb-testimoniales-report-v2.json')
fs.writeFileSync(report, JSON.stringify(results, null, 2))
console.log(`\nReporte JSON: ${report}`)

process.exitCode = ok ? 0 : 1
