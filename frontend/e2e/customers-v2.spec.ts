import { expect, test } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const here = path.dirname(fileURLToPath(import.meta.url));
test.describe('Customer V2', () => {
  test.use({ storageState: path.join(here, '.auth/staff.json') });
  test('opens the V2 list and a real V2 customer detail', async ({ page }) => {
    await page.goto('/customers'); await page.waitForLoadState('networkidle');
    const list = page.locator('main [data-ui="v2"]'); await expect(list.locator('table')).toBeVisible();
    const link = list.locator('a[href^="/customers/"]:not([href="/customers/new"])').first(); await expect(link).toBeVisible(); await link.click(); await page.waitForLoadState('networkidle');
    await expect(page.locator('main [data-ui="v2"] h1')).toBeVisible();
  });
  for (const width of [1440,1024,768,375] as const) test(`no overflow ${width}`, async ({page}) => { await page.setViewportSize({width,height:900}); await page.goto('/customers'); await page.waitForLoadState('networkidle'); expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true); });
});
