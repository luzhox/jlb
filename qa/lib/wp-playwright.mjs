/**
 * Helpers compartidos para tests visuales con Playwright contra wp-admin.
 *
 * Lee qa/.env (gitignoreado) para credenciales y paths. Si .env no existe,
 * cae a valores por defecto que pueden no funcionar — añadir qa/.env desde
 * qa/.env.example.
 *
 * Uso típico:
 *
 *   import { launchAdminSession, screenshot, ROOT, SCR_DIR } from './lib/wp-playwright.mjs';
 *
 *   const { browser, page } = await launchAdminSession();
 *   await page.goto(`${process.env.WP_SITE_URL}/wp-admin/edit.php?post_type=page`);
 *   await screenshot(page, 'mi-test');
 *   await browser.close();
 */
import { chromium } from 'playwright-core';
import path from 'node:path';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Raíz del tema. */
export const ROOT = path.resolve(__dirname, '..', '..');
/** Directorio para guardar screenshots. */
export const SCR_DIR = path.join(ROOT, 'qa');

/**
 * Carga qa/.env si existe y publica claves en process.env.
 * Implementación minimal para no depender de dotenv.
 */
function loadDotenv() {
    const envPath = path.join(ROOT, 'qa', '.env');
    if (!fs.existsSync(envPath)) return;
    const txt = fs.readFileSync(envPath, 'utf8');
    for (const line of txt.split('\n')) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) continue;
        const eq = trimmed.indexOf('=');
        if (eq === -1) continue;
        const key = trimmed.slice(0, eq).trim();
        const val = trimmed.slice(eq + 1).trim();
        if (!(key in process.env)) process.env[key] = val;
    }
}
loadDotenv();

const SITE     = process.env.WP_SITE_URL || 'http://jlb-school.local';
const USERNAME = process.env.WP_USER;
const PASSWORD = process.env.WP_PASS;
const CHROMIUM = process.env.PLAYWRIGHT_CHROMIUM;

/**
 * Lanza Chromium y devuelve { browser, ctx, page } sin loguear todavía.
 * Útil para tests que necesitan navegar a páginas públicas.
 */
export async function launchBrowser({ viewport = { width: 1440, height: 900 }, headless = true } = {}) {
    if (!CHROMIUM || !fs.existsSync(CHROMIUM)) {
        throw new Error(`Chromium no encontrado en PLAYWRIGHT_CHROMIUM=${CHROMIUM}. Revisa qa/.env.`);
    }
    const browser = await chromium.launch({ executablePath: CHROMIUM, headless });
    const ctx     = await browser.newContext({ viewport });
    const page    = await ctx.newPage();

    // Forward console logs útiles (errores, warnings, y todo lo que contenga "JLB").
    page.on('console', m => {
        const t = m.text();
        if (m.type() === 'error' || m.type() === 'warning' || t.includes('JLB')) {
            console.log(`  [browser ${m.type()}]`, t);
        }
    });
    page.on('pageerror', e => console.log('  [browser pageerror]', e.message));

    return { browser, ctx, page };
}

/**
 * Lanza Chromium y loguea en wp-admin con las credenciales de qa/.env.
 * Devuelve { browser, ctx, page } ya autenticado en wp-admin.
 *
 * Si el login falla, lanza error con la URL final como pista.
 */
export async function launchAdminSession(opts = {}) {
    if (!USERNAME || !PASSWORD) {
        throw new Error('Faltan WP_USER / WP_PASS en qa/.env (o env vars).');
    }
    const { browser, ctx, page } = await launchBrowser(opts);

    await page.goto(`${SITE}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await Promise.all([
        page.waitForLoadState('domcontentloaded'),
        page.click('#wp-submit'),
    ]);
    if (!/wp-admin/.test(page.url())) {
        await browser.close();
        throw new Error(`Login wp-admin falló. URL actual: ${page.url()}`);
    }

    // Dismiss el "welcome guide" de Gutenberg si aparece.
    try { await page.locator('button[aria-label="Cerrar"]').first().click({ timeout: 1500 }); } catch {}

    return { browser, ctx, page };
}

/**
 * Toma un screenshot fullPage y lo guarda en qa/<name>.png. Devuelve la ruta.
 */
export async function screenshot(page, name, opts = {}) {
    const file = path.join(SCR_DIR, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true, ...opts });
    console.log(`📸 ${file}`);
    return file;
}

/**
 * Helper: abre la primera página WP que matchee `titleRegex` (o la primera si no se pasa).
 * Devuelve la URL final.
 */
export async function openPageByTitle(page, titleRegex = null) {
    const SITE = process.env.WP_SITE_URL;
    await page.goto(`${SITE}/wp-admin/edit.php?post_type=page`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('table.wp-list-table tbody tr');
    const editLinks = await page.$$eval(
        'table.wp-list-table tbody tr a.row-title',
        els => els.map(a => ({ title: a.textContent.trim(), href: a.href }))
    );
    const target = titleRegex
        ? editLinks.find(p => titleRegex.test(p.title))
        : editLinks[0];
    if (!target) throw new Error(`No se encontró página${titleRegex ? ' que matchee ' + titleRegex : ''}.`);
    await page.goto(target.href, { waitUntil: 'domcontentloaded' });
    return target;
}

/** Export del path al sitio para que los scripts no lo re-importen. */
export const WP_SITE_URL = SITE;
