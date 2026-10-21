// QA calculadora jlb_cuota con montos 2027-2028. Uso: node qa/playwright-cuota-2027.mjs
import { chromium } from 'playwright-core';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const env = Object.fromEntries(fs.readFileSync(new URL('./.env', import.meta.url), 'utf8')
  .split('\n').filter((l) => /^\w+=/.test(l)).map((l) => [l.slice(0, l.indexOf('=')), l.slice(l.indexOf('=') + 1).trim()]));
const BASE = (process.env.WP_SITE_URL || env.WP_SITE_URL || 'http://jlb.local').replace(/\/$/, '');
const OUT = fileURLToPath(new URL('./live/', import.meta.url));
const browser = await chromium.launch({ executablePath: process.env.PLAYWRIGHT_CHROMIUM || env.PLAYWRIGHT_CHROMIUM });
const errors = [];

for (const width of [1440, 390]) {
  const page = await browser.newPage({ viewport: { width, height: 900 } });
  page.on('pageerror', (e) => errors.push(`${width} ${e.message}`));
  await page.goto(`${BASE}/admision/#cuota`, { waitUntil: 'networkidle' });
  const sec = page.locator('.jlb-cuota');
  await sec.scrollIntoViewIfNeeded();
  await page.evaluate(() => document.querySelectorAll('[data-gsap]').forEach((el) => { el.style.opacity = 1; el.style.transform = 'none'; }));
  const opts = await page.locator('[data-jlb-cuota-nivel] option').count();
  const cond = await page.locator('.jlb-cuota a.jlb-link-external').getAttribute('href');
  console.log(`[${width}] niveles=${opts} condiciones=${cond}`);
  for (const [nivel, modo, tag] of [['0', 'contado', 'inicial-contado'], ['3', 'cuotas', 'p3-cuotas'], ['11', 'cuotas', 's11-cuotas']]) {
    await page.selectOption('[data-jlb-cuota-nivel]', nivel);
    await page.selectOption('[data-jlb-cuota-modo]', modo);
    await page.click('[data-jlb-cuota-calc]');
    const res = await page.locator('[data-jlb-cuota-resultado]').textContent();
    const ah = await page.locator('[data-jlb-cuota-ahorro]').evaluate((el) => (el.hidden ? '' : el.textContent));
    console.log(`  ${tag}: "${res.trim()}" ${ah}`);
    await sec.screenshot({ path: `${OUT}cuota-2027-${tag}-${width}.png` });
  }
  await page.close();
}
console.log(errors.length ? `JS errors:\n${errors.join('\n')}` : 'JS errors: 0');
await browser.close();
