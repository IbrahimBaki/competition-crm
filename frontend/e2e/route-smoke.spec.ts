import { expect, test } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { monitorRuntime } from './support/runtime';

const here = path.dirname(fileURLToPath(import.meta.url));

const publicRoutes = ['/login', '/forgot-password', '/portal/login', '/portal/register', '/portal/help', '/chat'];
const staffRoutes = [
  '/', '/workspace', '/tickets', '/tickets/new', '/customers', '/customers/new', '/knowledge', '/knowledge/new',
  '/account', '/reports', '/dashboard', '/report-schedules', '/admin', '/admin/branches', '/admin/departments',
  '/admin/teams', '/admin/users', '/admin/roles', '/admin/ticket-catalogue', '/admin/sla-policies',
  '/admin/automation-rules', '/admin/channels', '/admin/settings', '/admin/audit', '/admin/data-protection',
  '/admin/integrations', '/admin/ai',
];
const portalRoutes = ['/portal/tickets', '/portal/tickets/new', '/portal/account'];
const expectedInvalidResourceRoutes = [
  '/reset-password?token=invalid&email=e2e%40example.test',
  '/invitations/invalid-token',
  '/portal/verify',
  '/portal/track/invalid-token',
  '/portal/help/invalid-article',
  '/forms/invalid-form',
  '/forms/submissions/invalid-token',
  '/this-route-does-not-exist',
];

for (const route of publicRoutes) {
  test(`public route ${route}`, async ({ page }, testInfo) => {
    const assertRuntime = monitorRuntime(page, testInfo);
    const response = await page.goto(route);
    expect(response?.status()).toBeLessThan(400);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('main, body').first()).toBeVisible();
    await assertRuntime();
  });
}

for (const route of expectedInvalidResourceRoutes) {
  test(`expected invalid-resource state ${route}`, async ({ page }) => {
    const response = await page.goto(route);
    expect(response?.status()).toBeLessThan(400);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('vite-error-overlay')).toHaveCount(0);
    await expect(page.getByText('Unexpected Application Error!')).toHaveCount(0);
    await expect(page.locator('body')).not.toBeEmpty();
  });
}

test.describe('staff routes', () => {
  test.use({ storageState: path.join(here, '.auth/staff.json') });
  for (const route of staffRoutes) {
    test(route, async ({ page }, testInfo) => {
      const assertRuntime = monitorRuntime(page, testInfo);
      const response = await page.goto(route);
      expect(response?.status()).toBeLessThan(400);
      await page.waitForLoadState('networkidle');
      await expect(page.locator('main')).toBeVisible();
      await expect(page.locator('h1, h2').first()).toBeVisible();
      await assertRuntime();
    });
  }

  test('valid dynamic detail routes from real backend resources', async ({ page }, testInfo) => {
    const assertRuntime = monitorRuntime(page, testInfo);
    const resources = [
      ['/tickets', '/tickets/'],
      ['/customers', '/customers/'],
      ['/knowledge', '/knowledge/'],
      ['/admin/branches', '/admin/branches/'],
      ['/admin/departments', '/admin/departments/'],
      ['/admin/teams', '/admin/teams/'],
      ['/admin/roles', '/admin/roles/'],
    ] as const;

    for (const [listRoute, routePrefix] of resources) {
      await page.goto(listRoute);
      await page.waitForLoadState('networkidle');
      const detailLink = page.locator(`a[href^="${routePrefix}"]`).filter({ hasNot: page.locator(`[href="${listRoute}"]`) }).first();
      await expect(detailLink, `${listRoute} should expose detail navigation`).toBeVisible();
      const href = await detailLink.getAttribute('href');
      expect(href).toBeTruthy();
      await page.goto(href!);
      await page.waitForLoadState('networkidle');
      await expect(page.locator('main')).toBeVisible();
      await expect(page.locator('h1, h2').first()).toBeVisible();
    }
    await assertRuntime();
  });
});

test.describe('portal routes', () => {
  test.use({ storageState: path.join(here, '.auth/portal.json') });
  for (const route of portalRoutes) {
    test(route, async ({ page }, testInfo) => {
      const assertRuntime = monitorRuntime(page, testInfo);
      const response = await page.goto(route);
      expect(response?.status()).toBeLessThan(400);
      await page.waitForLoadState('networkidle');
      await expect(page.locator('main, body').first()).toBeVisible();
      await assertRuntime();
    });
  }
});
