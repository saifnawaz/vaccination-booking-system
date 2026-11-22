/*
 * Screenshots of every main page at desktop and phone width, plus layout checks.
 *
 *   php artisan migrate:fresh --seed
 *   php artisan serve --port=8766
 *   node scripts/screenshots.mjs            (BASE_URL=… to point elsewhere)
 *
 * Saves PNGs to docs/screenshots/ and prints any page that scrolls sideways
 * (and the elements sticking out), console errors, and failed requests.
 * Pages render with reduced motion so screenshots never catch an animation midway.
 */
import { chromium } from '@playwright/test';
import { mkdir } from 'node:fs/promises';

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8766';
const OUT = 'docs/screenshots';
const VIEWPORTS = {
    desktop: { viewport: { width: 1440, height: 900 } },
    phone: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true },
};

const problems = [];

async function check(page, name) {
    const overflow = await page.evaluate(() => {
        const width = document.documentElement.clientWidth;
        if (document.documentElement.scrollWidth <= width + 1) return null;
        const culprits = [...document.querySelectorAll('body *')]
            .filter((el) => {
                const r = el.getBoundingClientRect();
                return r.width > 0 && r.right > width + 1 && getComputedStyle(el).position !== 'fixed';
            })
            .filter((el, _, all) => !all.some((other) => other !== el && other.contains(el)))
            .slice(0, 5)
            .map((el) => `${el.tagName.toLowerCase()}.${[...el.classList].slice(0, 4).join('.')} (right ${Math.round(el.getBoundingClientRect().right)}px)`);
        return { scrollWidth: document.documentElement.scrollWidth, width, culprits };
    });
    if (overflow) problems.push(`${name}: scrolls sideways (${overflow.scrollWidth}px > ${overflow.width}px) ← ${overflow.culprits.join(', ')}`);
}

async function shoot(page, name, url) {
    if (url) await page.goto(BASE + url, { waitUntil: 'networkidle' });
    await page.waitForTimeout(150);
    await check(page, name);
    // Phones: one screen, as a parent sees it (a full-page capture would paint the fixed
    // bottom tab bar halfway down the image). Desktop: the whole page.
    await page.screenshot({ path: `${OUT}/${name}.png`, fullPage: !name.endsWith('-phone') });
    console.log('  ✓', name);
}

async function login(page, email) {
    await page.goto(`${BASE}/login`);
    await page.fill('#email', email);
    await page.fill('#password', 'password');
    await Promise.all([page.waitForURL((u) => !u.pathname.startsWith('/login')), page.click('button[type=submit]')]);
}

async function firstHref(page, selector) {
    return page.locator(selector).first().getAttribute('href');
}

async function run(browser, vp, options) {
    const tag = (n) => `${n}-${vp}`;
    const context = async () => {
        const ctx = await browser.newContext({ ...options, reducedMotion: 'reduce' });
        const page = await ctx.newPage();
        page.on('console', (m) => m.type() === 'error' && problems.push(`${vp} console: ${m.text()}`));
        page.on('pageerror', (e) => problems.push(`${vp} page error: ${e.message}`));
        page.on('response', (r) => r.status() >= 500 && problems.push(`${vp} ${r.status()} ${r.url()}`));
        return [ctx, page];
    };

    // Guests
    let [ctx, page] = await context();
    for (const [n, u] of [['landing', '/'], ['login', '/login'], ['register', '/register'], ['register-hospital', '/register/hospital']]) {
        await shoot(page, tag(`guest-${n}`), u);
    }
    await ctx.close();

    // Parent (Yusuf and Zara: overdue and missed doses)
    [ctx, page] = await context();
    await login(page, 'parent4@test.com');
    await shoot(page, tag('parent-home'), '/parent');
    await shoot(page, tag('parent-children'), '/parent/children');
    const child = await firstHref(page, 'main a[href*="/parent/children/"]:not([href$="/create"])');
    await shoot(page, tag('parent-child-schedule'), new URL(child).pathname);
    await shoot(page, tag('parent-certificate'), new URL(child).pathname + '/certificate');
    await shoot(page, tag('parent-book-children'), '/parent/book');
    await page.goto(BASE + '/parent/book/' + new URL(child).pathname.split('/').pop(), { waitUntil: 'networkidle' });
    await shoot(page, tag('parent-book-doses'));
    await Promise.all([page.waitForURL(/\/hospitals/), page.click('main form button[type=submit]')]);
    await shoot(page, tag('parent-book-hospitals'));
    await Promise.all([page.waitForURL(/\/hospitals\/\d+/), page.locator('main ul a').first().click()]);
    await shoot(page, tag('parent-book-slots'));
    await Promise.all([page.waitForURL(/\/confirm/), page.locator('main ul li a').first().click()]);
    await shoot(page, tag('parent-book-confirm'));
    await shoot(page, tag('parent-appointments'), '/parent/appointments');
    await shoot(page, tag('parent-notifications'), '/notifications');
    await shoot(page, tag('parent-account'), '/profile');
    await ctx.close();

    // Hospital
    [ctx, page] = await context();
    await login(page, 'hospital1@test.com');
    await shoot(page, tag('hospital-overview'), '/hospital');
    await shoot(page, tag('hospital-appointments'), '/hospital/appointments');
    const appointment = await firstHref(page, 'main a[href*="/hospital/appointments/"]');
    if (appointment) await shoot(page, tag('hospital-appointment'), new URL(appointment).pathname);
    await shoot(page, tag('hospital-stock'), '/hospital/stock');
    await shoot(page, tag('hospital-slots'), '/hospital/slots');
    await shoot(page, tag('hospital-slots-bulk'), '/hospital/slots/bulk');
    await ctx.close();

    // Admin
    [ctx, page] = await context();
    await login(page, 'admin@test.com');
    await shoot(page, tag('admin-overview'), '/admin');
    await shoot(page, tag('admin-hospitals'), '/admin/hospitals');
    const hospital = await firstHref(page, 'main a[href*="/admin/hospitals/"]');
    await shoot(page, tag('admin-hospital'), new URL(hospital).pathname);
    await shoot(page, tag('admin-vaccines'), '/admin/vaccines');
    await shoot(page, tag('admin-schedule'), '/admin/vaccines/schedule');
    await shoot(page, tag('admin-activity'), '/admin/activity');
    await ctx.close();
}

await mkdir(OUT, { recursive: true });
const browser = await chromium.launch();
for (const [vp, options] of Object.entries(VIEWPORTS)) {
    console.log(vp);
    await run(browser, vp, options);
}

// The login logo intro, mid-animation (motion allowed for this one).
const intro = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const introPage = await intro.newPage();
await introPage.goto(`${BASE}/login`);
await introPage.waitForTimeout(260);
await introPage.screenshot({ path: `${OUT}/guest-login-intro-desktop.png`, clip: { x: 520, y: 0, width: 400, height: 260 } });
await intro.close();

await browser.close();

if (problems.length) {
    console.log(`\n${problems.length} problem(s):`);
    [...new Set(problems)].forEach((p) => console.log(' ✗', p));
    process.exitCode = 1;
} else {
    console.log('\nNo overflow, console errors or server errors.');
}
