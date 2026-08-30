import { expect, type Page, type TestInfo } from '@playwright/test';

function isExpectedResponse(status: number, url: string) {
  return status === 401 && (/\/api\/v1\/auth\/me(?:\?|$)/.test(url) || /\/api\/v1\/portal\/me(?:\?|$)/.test(url));
}

export function monitorRuntime(page: Page, testInfo: TestInfo) {
  const failures: string[] = [];
  page.on('pageerror', (error) => failures.push(`pageerror: ${error.message}`));
  page.on('console', (message) => {
    if (message.type() === 'error' && !message.text().startsWith('Failed to load resource:')) {
      failures.push(`console: ${message.text()}`);
    }
  });
  page.on('response', (response) => {
    if (response.status() >= 400 && !isExpectedResponse(response.status(), response.url())) {
      failures.push(`http ${response.status()}: ${response.url()}`);
    }
  });
  page.on('requestfailed', (request) => {
    if (request.failure()?.errorText !== 'net::ERR_ABORTED') {
      failures.push(`requestfailed: ${request.url()} ${request.failure()?.errorText ?? ''}`);
    }
  });

  return async () => {
    await expect(page.locator('body')).not.toContainText(/Network Error|Something went wrong/i);
    await expect(page.locator('vite-error-overlay')).toHaveCount(0);
    if (failures.length) {
      await testInfo.attach('runtime-failures', { body: failures.join('\n'), contentType: 'text/plain' });
    }
    expect(failures, failures.join('\n')).toEqual([]);
  };
}
