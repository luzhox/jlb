import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://jlb-school.local/wp-login.php', { waitUntil: 'networkidle' });
await page.waitForTimeout(600);
const info = await page.evaluate(() => ({
  slides: document.querySelectorAll('.jlb-login-bg__slide').length,
  active: document.querySelector('.jlb-login-bg__slide.is-active')?.style.backgroundImage.slice(0, 90),
}));
console.log('slides:', info.slides, '| activo:', info.active);
await page.screenshot({ path: 'qa/live/login-bg-slide1-1440.png' });
await page.waitForTimeout(6500);
const info2 = await page.evaluate(() => ({
  activeIdx: [...document.querySelectorAll('.jlb-login-bg__slide')].findIndex(s => s.classList.contains('is-active')),
}));
console.log('tras 6.5s activo idx:', info2.activeIdx);
await page.screenshot({ path: 'qa/live/login-bg-slide2-1440.png' });
await browser.close();
