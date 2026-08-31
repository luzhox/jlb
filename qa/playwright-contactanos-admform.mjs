import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

for (const url of ['http://jlb-school.local/talleres/taller-de-danza/', 'http://jlb-school.local/niveles/inicial/']) {
  await page.goto(url, { waitUntil: 'networkidle' });
  const href = await page.$eval('.jlb-adm-hero a:has-text("Contáctanos")', a => a.getAttribute('href'));
  await page.click('.jlb-adm-hero a:has-text("Contáctanos")');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(900);
  const m = await page.evaluate(() => {
    const h = document.querySelector('.jlb-header').getBoundingClientRect().bottom;
    const s = document.querySelector('#admision-form');
    return { url: location.pathname + location.hash, headerBottom: h, sectionTop: s ? s.getBoundingClientRect().top : null };
  });
  console.log(url.split('/')[4], '| href:', href, '|', JSON.stringify(m), m.sectionTop !== null && m.sectionTop >= m.headerBottom ? 'OK' : 'FAIL');
}
await page.screenshot({ path: 'qa/live/contactanos-admform-1440.png' });
await browser.close();
