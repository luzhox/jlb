import { chromium } from 'playwright-core';
import fs from 'node:fs';
const env = Object.fromEntries(fs.readFileSync('qa/.env','utf8').split('\n').filter(l=>l.includes('=')&&!l.trim().startsWith('#')).map(l=>[l.slice(0,l.indexOf('=')).trim(), l.slice(l.indexOf('=')+1).trim()]));
const browser = await chromium.launch({ executablePath: env.PLAYWRIGHT_CHROMIUM });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://jlb-school.local/wp-login.php', { waitUntil: 'networkidle' });
await page.waitForTimeout(400);
await page.screenshot({ path: 'qa/live/login-jlb-1440.png' });
// error state
await page.fill('#user_login', 'x'); await page.fill('#user_pass', 'x');
await page.click('#wp-submit');
await page.waitForLoadState('networkidle');
await page.waitForTimeout(400);
await page.screenshot({ path: 'qa/live/login-jlb-error-1440.png' });
const m = await page.setViewportSize({ width: 390, height: 844 });
await page.goto('http://jlb-school.local/wp-login.php', { waitUntil: 'networkidle' });
await page.screenshot({ path: 'qa/live/login-jlb-390.png' });
await browser.close();
