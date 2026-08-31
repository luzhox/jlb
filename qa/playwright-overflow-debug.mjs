import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
await page.goto('http://jlb-school.local/', { waitUntil: 'networkidle' });
await page.waitForTimeout(600);
const culprits = await page.evaluate(() => {
  const vw = document.documentElement.clientWidth;
  const bad = [];
  document.querySelectorAll('body *').forEach(el => {
    const r = el.getBoundingClientRect();
    if (r.right > vw + 1 || r.left < -1) {
      const cls = (el.className && typeof el.className === 'string') ? el.className.slice(0, 60) : el.tagName;
      bad.push(`${el.tagName}.${cls} L${Math.round(r.left)} R${Math.round(r.right)}`);
    }
  });
  return bad.slice(0, 15);
});
console.log(culprits.join('\n'));
await browser.close();
