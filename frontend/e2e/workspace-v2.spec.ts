import { expect, test } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const widths = [1440, 1024, 768, 375] as const;
const overflow = async (page: import('@playwright/test').Page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);

test.describe('Signal Ledger Workspace V2', () => {
  test.use({ storageState: path.join(here, '.auth/staff.json') });

  test('keeps both aliases V2, links to existing records, and isolates a V1 sibling', async ({ page }) => {
    for (const route of ['/', '/workspace']) {
      await page.goto(route);
      await page.waitForLoadState('networkidle');
      await expect(page.locator('[data-ui="v2"] h1')).toBeVisible();
      await expect(page.locator('[data-ui="v2"] h1')).toHaveText(/Workspace/);
    }
    const ticket = page.locator('[data-ui="v2"] a[href^="/tickets/"]').first();
    if (await ticket.count()) {
      await ticket.click();
      await expect(page).toHaveURL(/\/tickets\//);
      await page.goBack();
      await expect(page.locator('[data-ui="v2"] h1')).toBeVisible();
    }
    await page.goto('/tickets');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('main [data-ui="v2"]')).toHaveCount(0);
    await expect(page.locator('[data-ui="v2"] nav')).toBeVisible();
  });

  for (const width of widths) {
    test(`has no document overflow at ${width}px`, async ({ page }) => {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/workspace');
      await page.waitForLoadState('networkidle');
      await expect(page.locator('[data-ui="v2"] h1')).toBeVisible();
      expect(await overflow(page)).toBe(true);
    });
  }

  for (const [width, language] of [[1440, 'ar'], [768, 'ar'], [375, 'en'], [375, 'ar']] as const) {
    test(`${width}px ${language} Workspace composition`, async ({ page }) => {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/workspace');
      await page.evaluate((locale) => localStorage.setItem('locale', locale), language);
      await page.reload();
      await page.waitForLoadState('networkidle');
      const boundary = page.locator('main [data-ui="v2"]');
      await expect(boundary).toBeVisible();
      await expect(boundary).toHaveAttribute('dir', language === 'ar' ? 'rtl' : 'ltr');
      expect(await overflow(page)).toBe(true);
    });
  }
});
