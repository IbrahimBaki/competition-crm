import { expect, test } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { monitorRuntime } from './support/runtime';

const here = path.dirname(fileURLToPath(import.meta.url));
const unique = () => `${Date.now()}-${Math.random().toString(16).slice(2)}`;

test.describe('staff create workflows', () => {
  test.use({ storageState: path.join(here, '.auth/staff.json') });

  test('creates a customer through the UI and opens its real detail page', async ({ page }, testInfo) => {
    const assertRuntime = monitorRuntime(page, testInfo);
    await page.goto('/customers/new');
    await page.getByRole('button', { name: 'Create customer' }).click();
    await expect(page.getByLabel('Customer name')).toBeFocused();

    const name = `E2E Customer ${unique()}`;
    await page.getByLabel('Customer name').fill(name);
    const createResponse = page.waitForResponse((response) => response.url().endsWith('/api/v1/customers') && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Create customer' }).click();
    expect((await createResponse).status()).toBe(201);
    await expect(page).toHaveURL(/\/customers\/[0-9a-f-]{36}$/);
    await expect(page.getByText(name, { exact: true })).toBeVisible();
    await page.waitForLoadState('networkidle');
    await assertRuntime();
  });

  test('creates a staff ticket through the UI', async ({ page }, testInfo) => {
    const assertRuntime = monitorRuntime(page, testInfo);
    await page.goto('/tickets/new');
    await page.getByLabel('Customer').selectOption({ index: 1 });
    await page.getByLabel('Department').selectOption({ index: 1 });
    const subject = `E2E staff ticket ${unique()}`;
    await page.getByLabel('Subject').fill(subject);
    await page.getByLabel('Message').fill('Created by the release closure browser suite.');
    const createResponse = page.waitForResponse((response) => response.url().endsWith('/api/v1/tickets') && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Create ticket' }).click();
    expect((await createResponse).status()).toBe(201);
    await expect(page).toHaveURL(/\/tickets\/[0-9a-f-]{36}$/);
    await expect(page.getByText(subject, { exact: true })).toBeVisible();
    await page.waitForLoadState('networkidle');
    await assertRuntime();
  });

  test('creates and updates a bilingual knowledge article through the UI', async ({ page }, testInfo) => {
    const assertRuntime = monitorRuntime(page, testInfo);
    await page.goto('/knowledge/new');
    const suffix = unique();
    await page.getByLabel('English title').fill(`E2E article ${suffix}`);
    await page.getByLabel('Arabic title').fill(`مقال اختبار ${suffix}`);
    await page.getByLabel('English article').fill('Verified English support guidance.');
    await page.getByLabel('Arabic article').fill('إرشادات دعم عربية تم التحقق منها.');
    const createResponse = page.waitForResponse((response) => response.url().endsWith('/api/v1/knowledge/articles') && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Create article' }).click();
    expect((await createResponse).status()).toBe(201);
    await expect(page).toHaveURL(/\/knowledge\/[0-9a-f-]{36}$/);
    await expect(page.getByText(`E2E article ${suffix}`, { exact: true })).toBeVisible();

    await page.getByRole('link', { name: /edit article/i }).click();
    const updatedTitle = `E2E updated article ${suffix}`;
    await page.getByLabel('English title').fill(updatedTitle);
    const updateResponse = page.waitForResponse((response) => /\/api\/v1\/knowledge\/articles\/[0-9a-f-]{36}$/.test(response.url()) && response.request().method() === 'PATCH');
    await page.getByRole('button', { name: 'Save changes' }).click();
    expect((await updateResponse).status()).toBe(200);
    await expect(page.getByText(updatedTitle, { exact: true })).toBeVisible();
    await page.waitForLoadState('networkidle');
    await assertRuntime();
  });
});

test.describe('portal workflow', () => {
  test.use({ storageState: path.join(here, '.auth/portal.json') });

  test('creates a ticket, renders it, and sends a reply through the real API', async ({ page }, testInfo) => {
    await page.goto('/portal/tickets/new');
    const validationResponse = page.waitForResponse((response) => response.url().endsWith('/api/v1/portal/tickets') && response.request().method() === 'POST');
    await page.getByRole('button', { name: /submit|create|send/i }).click();
    expect((await validationResponse).status()).toBe(422);
    await expect(page.getByText(/subject.*required/i).first()).toBeVisible();
    const assertRuntime = monitorRuntime(page, testInfo);

    const subject = `E2E portal ticket ${unique()}`;
    await page.getByLabel(/subject/i).fill(subject);
    await page.getByLabel(/description/i).fill('Portal ticket created by deterministic browser verification.');
    const createResponse = page.waitForResponse((response) => response.url().endsWith('/api/v1/portal/tickets') && response.request().method() === 'POST');
    await page.getByRole('button', { name: /submit|create|send/i }).click();
    expect((await createResponse).status()).toBe(201);
    await expect(page).toHaveURL(/\/portal\/tickets\/[0-9a-f-]{36}$/);
    await expect(page.getByText(subject, { exact: true })).toBeVisible();

    const reply = `E2E reply ${unique()}`;
    await page.getByLabel('Add a reply').fill(reply);
    const replyResponse = page.waitForResponse((response) => /\/api\/v1\/portal\/tickets\/.+\/messages$/.test(response.url()) && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Send reply' }).click();
    expect((await replyResponse).status()).toBe(201);
    await expect(page.getByText(reply, { exact: true })).toBeVisible();
    await page.waitForLoadState('networkidle');
    await assertRuntime();
  });
});
