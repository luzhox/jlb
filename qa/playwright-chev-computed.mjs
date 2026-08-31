import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://jlb-school.local/', { waitUntil: 'networkidle' });
const r = await page.evaluate(() => {
  const p = document.querySelector('.jlb-hero .swiper-button-prev path');
  const cs = getComputedStyle(p);
  const svg = p.closest('svg');
  const scs = getComputedStyle(svg);
  return { fill: cs.fill, stroke: cs.stroke, strokeWidth: cs.strokeWidth, svgFill: scs.fill,
           attrFill: svg.getAttribute('fill'), pathD: p.getAttribute('d'), pathAttrStroke: p.getAttribute('stroke') };
});
console.log(JSON.stringify(r, null, 1));
await browser.close();
