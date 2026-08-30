import { chromium, type FullConfig } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import path from 'node:path';

const password = 'E2eSupport!2026';

export default async function globalSetup(config: FullConfig) {
  const root = path.resolve(import.meta.dirname, '../..');
  mkdirSync(path.join(import.meta.dirname, '.auth'), { recursive: true });
  execFileSync('docker', [
    'exec', 'azm-php82', 'bash', '-lc',
    "cd /var/www/html/competition-crm && php artisan db:seed --class='Database\\Seeders\\E2eTestSeeder' --force",
  ], { cwd: root, stdio: 'inherit' });

  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const baseURL = config.projects[0].use.baseURL as string;

  const staff = await browser.newContext();
  const staffPage = await staff.newPage();
  await staffPage.goto(`${baseURL}/login`);
  await staffPage.getByLabel(/email/i).fill('e2e.admin@example.test');
  await staffPage.getByLabel(/password/i).fill(password);
  await Promise.all([
    staffPage.waitForURL((url) => url.pathname === '/'),
    staffPage.getByRole('button', { name: /sign in/i }).click(),
  ]);
  await staff.storageState({ path: path.join(import.meta.dirname, '.auth/staff.json') });
  await staff.close();

  const portal = await browser.newContext();
  const portalPage = await portal.newPage();
  await portalPage.goto(`${baseURL}/portal/login`);
  await portalPage.getByLabel(/email/i).fill('e2e.portal@example.test');
  await portalPage.getByLabel(/password/i).fill(password);
  await Promise.all([
    portalPage.waitForURL(/\/portal\/tickets/),
    portalPage.getByRole('button', { name: /sign in/i }).click(),
  ]);
  await portal.storageState({ path: path.join(import.meta.dirname, '.auth/portal.json') });
  await portal.close();
  await browser.close();
}
