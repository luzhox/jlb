import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://jlb-school.local/', { waitUntil: 'networkidle' });
await page.waitForTimeout(500);
const prev = await page.$('.jlb-hero .swiper-button-prev');
const next = await page.$('.jlb-hero .swiper-button-next');
console.log('prev box:', JSON.stringify(await prev.boundingBox()));
console.log('next box:', JSON.stringify(await next.boundingBox()));
const styles = await page.evaluate(() => {
  const b = document.querySelector('.jlb-hero .swiper-button-prev');
  const cs = getComputedStyle(b);
  const svg = b.querySelector('svg');
  return { w: cs.width, h: cs.height, bg: cs.backgroundColor, radius: cs.borderRadius, color: cs.color,
           svgW: svg ? getComputedStyle(svg).width : null, svgH: svg ? getComputedStyle(svg).height : null,
           inner: b.innerHTML.slice(0, 200) };
});
console.log(JSON.stringify(styles, null, 1));
await page.screenshot({ path: 'qa/live/hero-arrow-prev-detail.png', clip: { x: 0, y: 380, width: 140, height: 140 } });
await page.screenshot({ path: 'qa/live/hero-arrow-next-detail.png', clip: { x: 1300, y: 380, width: 140, height: 140 } });
await browser.close();
