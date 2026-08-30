# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: route-smoke.spec.ts >> staff routes >> valid dynamic detail routes from real backend resources
- Location: e2e/route-smoke.spec.ts:64:3

# Error details

```
Error: /admin/roles should expose detail navigation

expect(locator).toBeVisible() failed

Locator: locator('a[href^="/admin/roles/"]').filter({ hasNot: locator('[href="/admin/roles"]') }).first()
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - /admin/roles should expose detail navigation with timeout 10000ms
  - waiting for locator('a[href^="/admin/roles/"]').filter({ hasNot: locator('[href="/admin/roles"]') }).first()

```

```yaml
- main:
  - paragraph: Operations workspace
  - heading "Support CRM" [level=1]
  - heading "Sign In" [level=2]
  - text: Email
  - textbox "Email"
  - text: Password
  - textbox "Password"
  - button "Sign In"
  - link "Forgot password?":
    - /url: /forgot-password
  - link "Customer portal":
    - /url: /portal/login
```

# Test source

```ts
  1   | import { expect, test } from '@playwright/test';
  2   | import path from 'node:path';
  3   | import { fileURLToPath } from 'node:url';
  4   | import { monitorRuntime } from './support/runtime';
  5   | 
  6   | const here = path.dirname(fileURLToPath(import.meta.url));
  7   | 
  8   | const publicRoutes = ['/login', '/forgot-password', '/portal/login', '/portal/register', '/portal/help', '/chat'];
  9   | const staffRoutes = [
  10  |   '/', '/workspace', '/tickets', '/tickets/new', '/customers', '/customers/new', '/knowledge', '/knowledge/new',
  11  |   '/account', '/reports', '/dashboard', '/report-schedules', '/admin', '/admin/branches', '/admin/departments',
  12  |   '/admin/teams', '/admin/users', '/admin/roles', '/admin/ticket-catalogue', '/admin/sla-policies',
  13  |   '/admin/automation-rules', '/admin/channels', '/admin/settings', '/admin/audit', '/admin/data-protection',
  14  |   '/admin/integrations', '/admin/ai',
  15  | ];
  16  | const portalRoutes = ['/portal/tickets', '/portal/tickets/new', '/portal/account'];
  17  | const expectedInvalidResourceRoutes = [
  18  |   '/reset-password?token=invalid&email=e2e%40example.test',
  19  |   '/invitations/invalid-token',
  20  |   '/portal/verify',
  21  |   '/portal/track/invalid-token',
  22  |   '/portal/help/invalid-article',
  23  |   '/forms/invalid-form',
  24  |   '/forms/submissions/invalid-token',
  25  |   '/this-route-does-not-exist',
  26  | ];
  27  | 
  28  | for (const route of publicRoutes) {
  29  |   test(`public route ${route}`, async ({ page }, testInfo) => {
  30  |     const assertRuntime = monitorRuntime(page, testInfo);
  31  |     const response = await page.goto(route);
  32  |     expect(response?.status()).toBeLessThan(400);
  33  |     await page.waitForLoadState('networkidle');
  34  |     await expect(page.locator('main, body').first()).toBeVisible();
  35  |     await assertRuntime();
  36  |   });
  37  | }
  38  | 
  39  | for (const route of expectedInvalidResourceRoutes) {
  40  |   test(`expected invalid-resource state ${route}`, async ({ page }) => {
  41  |     const response = await page.goto(route);
  42  |     expect(response?.status()).toBeLessThan(400);
  43  |     await page.waitForLoadState('networkidle');
  44  |     await expect(page.locator('vite-error-overlay')).toHaveCount(0);
  45  |     await expect(page.getByText('Unexpected Application Error!')).toHaveCount(0);
  46  |     await expect(page.locator('body')).not.toBeEmpty();
  47  |   });
  48  | }
  49  | 
  50  | test.describe('staff routes', () => {
  51  |   test.use({ storageState: path.join(here, '.auth/staff.json') });
  52  |   for (const route of staffRoutes) {
  53  |     test(route, async ({ page }, testInfo) => {
  54  |       const assertRuntime = monitorRuntime(page, testInfo);
  55  |       const response = await page.goto(route);
  56  |       expect(response?.status()).toBeLessThan(400);
  57  |       await page.waitForLoadState('networkidle');
  58  |       await expect(page.locator('main')).toBeVisible();
  59  |       await expect(page.locator('h1, h2').first()).toBeVisible();
  60  |       await assertRuntime();
  61  |     });
  62  |   }
  63  | 
  64  |   test('valid dynamic detail routes from real backend resources', async ({ page }, testInfo) => {
  65  |     const assertRuntime = monitorRuntime(page, testInfo);
  66  |     const resources = [
  67  |       ['/tickets', '/tickets/'],
  68  |       ['/customers', '/customers/'],
  69  |       ['/knowledge', '/knowledge/'],
  70  |       ['/admin/branches', '/admin/branches/'],
  71  |       ['/admin/departments', '/admin/departments/'],
  72  |       ['/admin/teams', '/admin/teams/'],
  73  |       ['/admin/roles', '/admin/roles/'],
  74  |     ] as const;
  75  | 
  76  |     for (const [listRoute, routePrefix] of resources) {
  77  |       await page.goto(listRoute);
  78  |       await page.waitForLoadState('networkidle');
  79  |       const detailLink = page.locator(`a[href^="${routePrefix}"]`).filter({ hasNot: page.locator(`[href="${listRoute}"]`) }).first();
> 80  |       await expect(detailLink, `${listRoute} should expose detail navigation`).toBeVisible();
      |                                                                                ^ Error: /admin/roles should expose detail navigation
  81  |       const href = await detailLink.getAttribute('href');
  82  |       expect(href).toBeTruthy();
  83  |       await page.goto(href!);
  84  |       await page.waitForLoadState('networkidle');
  85  |       await expect(page.locator('main')).toBeVisible();
  86  |       await expect(page.locator('h1, h2').first()).toBeVisible();
  87  |     }
  88  |     await assertRuntime();
  89  |   });
  90  | });
  91  | 
  92  | test.describe('portal routes', () => {
  93  |   test.use({ storageState: path.join(here, '.auth/portal.json') });
  94  |   for (const route of portalRoutes) {
  95  |     test(route, async ({ page }, testInfo) => {
  96  |       const assertRuntime = monitorRuntime(page, testInfo);
  97  |       const response = await page.goto(route);
  98  |       expect(response?.status()).toBeLessThan(400);
  99  |       await page.waitForLoadState('networkidle');
  100 |       await expect(page.locator('main, body').first()).toBeVisible();
  101 |       await assertRuntime();
  102 |     });
  103 |   }
  104 | });
  105 | 
```