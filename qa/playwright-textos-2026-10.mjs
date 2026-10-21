// QA de textos corregidos (2026-10-08): Nosotros (principios, historia, nota IB,
// infraestructura) y Admisión (proceso 3 pasos). Uso: node qa/playwright-textos-2026-10.mjs
import { chromium } from 'playwright-core';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

// qa/.env (gitignoreado): WP_SITE_URL + PLAYWRIGHT_CHROMIUM.
const env = Object.fromEntries(fs.readFileSync(new URL('./.env', import.meta.url), 'utf8')
  .split('\n').filter((l) => /^\w+=/.test(l)).map((l) => [l.slice(0, l.indexOf('=')), l.slice(l.indexOf('=') + 1).trim()]));
const BASE = (process.env.WP_SITE_URL || env.WP_SITE_URL || 'http://jlb.local').replace(/\/$/, '');
const OUT = fileURLToPath(new URL('./live/', import.meta.url));
const browser = await chromium.launch({ executablePath: process.env.PLAYWRIGHT_CHROMIUM || env.PLAYWRIGHT_CHROMIUM });
const errors = [];

for (const width of [1440, 390]) {
  const page = await browser.newPage({ viewport: { width, height: 900 } });
  page.on('pageerror', (e) => errors.push(`${width} ${e.message}`));

  await page.goto(`${BASE}/nosotros/`, { waitUntil: 'networkidle' });
  await page.evaluate(() => document.querySelectorAll('[data-gsap],[data-gsap-batch] > *').forEach((el) => { el.style.opacity = 1; el.style.transform = 'none'; }));
  // Fuerza la carga de imágenes lazy antes de la captura full-page.
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); } window.scrollTo(0, 0); });
  await page.waitForLoadState('networkidle');
  const cards = await page.locator('.jlb-principios__card').count();
  const hitos = await page.locator('.jlb-timeline__item').count();
  const nota = await page.locator('.jlb-timeline__nota a[href*="ibo.org"]').count();
  const infra = await page.locator('.jlb-experiencias__section-title', { hasText: 'Infraestructura' }).count();
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
  console.log(`[nosotros ${width}] principios=${cards} hitos=${hitos} notaIB=${nota} infra=${infra} overflow=${overflow}`);
  await page.screenshot({ path: `${OUT}nosotros-textos-${width}.png`, fullPage: true });

  await page.goto(`${BASE}/admision/#proceso`, { waitUntil: 'networkidle' });
  const proc = page.locator('.jlb-proceso');
  await proc.scrollIntoViewIfNeeded();
  await page.evaluate(() => document.querySelectorAll('[data-gsap]').forEach((el) => { el.style.opacity = 1; el.style.transform = 'none'; }));
  const tabs = await page.locator('.jlb-proceso__tab').allTextContents();
  console.log(`[admision ${width}] tabs=${JSON.stringify(tabs.map((t) => t.trim()))} descargas=${await page.locator('.jlb-proceso__descargas a').count()}`);
  for (let i = 0; i < tabs.length; i++) {
    await page.locator('.jlb-proceso__tab').nth(i).click();
    await proc.screenshot({ path: `${OUT}admision-proceso-paso${i + 1}-${width}.png` });
  }
  await page.close();
}
console.log(errors.length ? `JS errors:\n${errors.join('\n')}` : 'JS errors: 0');
await browser.close();
