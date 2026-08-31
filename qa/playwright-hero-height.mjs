import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });

for (const [w, h] of [[1440, 900], [834, 1100], [390, 844]]) {
  const page = await browser.newPage({ viewport: { width: w, height: h } });
  await page.goto('http://jlb-school.local/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(600);
  const m = await page.evaluate(() => {
    const hero = document.querySelector('.jlb-hero');
    const slides = [...document.querySelectorAll('.jlb-hero__slide')].map(s => Math.round(s.getBoundingClientRect().height));
    const imgs = [...document.querySelectorAll('.jlb-hero__media img')].map(i => `${i.naturalWidth}x${i.naturalHeight}`);
    return { hero: Math.round(hero.getBoundingClientRect().height), slides, imgs, canScrollX: (window.scrollTo(80,0), window.scrollX > 0) };
  });
  console.log(`${w}px:`, JSON.stringify(m));
  if (w === 1440) await page.screenshot({ path: 'qa/live/hero-height-fixed-1440.png' });
  if (w === 390) await page.screenshot({ path: 'qa/live/hero-height-fixed-390.png' });
  await page.close();
}
await browser.close();
