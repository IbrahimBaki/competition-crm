import { expect, test } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const widths = [375, 768, 1024, 1440] as const;

async function expectNoDocumentOverflow(page: import('@playwright/test').Page) {
  const overflow = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(overflow.document, `document width ${overflow.document} exceeds viewport ${overflow.viewport}`).toBeLessThanOrEqual(overflow.viewport);
}

test.describe('responsive staff patterns', () => {
  test.use({ storageState: path.join(here, '.auth/staff.json') });

  for (const width of widths) {
    test(`${width}px list, table, and form patterns`, async ({ page }) => {
      await page.setViewportSize({ width, height: 900 });
      for (const route of ['/tickets', '/admin/users', '/tickets/new']) {
        await page.goto(route);
        await page.waitForLoadState('networkidle');
        await expect(page.locator('main')).toBeVisible();
        await expectNoDocumentOverflow(page);
      }
      await expect(page.getByRole('button', { name: 'Create ticket' })).toBeVisible();
    });
  }
});

test.describe('responsive portal and public patterns', () => {
  test.use({ storageState: path.join(here, '.auth/portal.json') });

  for (const width of widths) {
    test(`${width}px portal form and public chat`, async ({ page }) => {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/portal/tickets/new');
      await page.waitForLoadState('networkidle');
      await expect(page.getByRole('button', { name: /submit/i })).toBeVisible();
      await expectNoDocumentOverflow(page);
      await page.goto('/chat');
      await page.waitForLoadState('networkidle');
      await expect(page.locator('main, body').first()).toBeVisible();
      await expectNoDocumentOverflow(page);
    });
  }
});
