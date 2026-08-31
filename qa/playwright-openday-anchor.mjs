import { chromium } from 'playwright-core';

import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://jlb-school.local/open-day/', { waitUntil: 'networkidle' });

// 1) Años de admisión
const anios = await page.$$eval('[name="anio_admision"]', els => els.map(e => e.value));
console.log('AÑOS:', anios.join(', '));

// 2) Click "Inscríbete ahora" → primera línea visible bajo header
await page.click('a[href="#open-day-form"]');
await page.waitForTimeout(800);
const m = await page.evaluate(() => {
  const header = document.querySelector('.jlb-header');
  const sec = document.querySelector('#open-day-form');
  const firstField = sec.querySelector('.jlb-openday__grid label');
  return {
    headerBottom: header.getBoundingClientRect().bottom,
    sectionTop: sec.getBoundingClientRect().top,
    firstFieldTop: firstField.getBoundingClientRect().top,
  };
});
console.log('MÉTRICAS:', JSON.stringify(m));
console.log(m.sectionTop >= m.headerBottom ? 'OK: sección arranca debajo del header' : 'FAIL: header tapa la sección');

await page.screenshot({ path: 'qa/live/openday-anchor-fixed-1440.png' });

// 3) Mobile 390
await page.setViewportSize({ width: 390, height: 844 });
await page.goto('http://jlb-school.local/open-day/', { waitUntil: 'networkidle' });
await page.click('a[href="#open-day-form"]');
await page.waitForTimeout(800);
const m2 = await page.evaluate(() => {
  const header = document.querySelector('.jlb-header');
  const sec = document.querySelector('#open-day-form');
  return { headerBottom: header.getBoundingClientRect().bottom, sectionTop: sec.getBoundingClientRect().top };
});
console.log('MOBILE:', JSON.stringify(m2), m2.sectionTop >= m2.headerBottom ? 'OK' : 'FAIL');
await page.screenshot({ path: 'qa/live/openday-anchor-fixed-390.png' });

await browser.close();
