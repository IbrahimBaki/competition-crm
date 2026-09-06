import { expect, test, type Page } from "@playwright/test";
import path from "node:path";
import { fileURLToPath } from "node:url";

const here = path.dirname(fileURLToPath(import.meta.url));
const noOverflow = (page: Page) =>
    page.evaluate(
        () =>
            document.documentElement.scrollWidth <=
            document.documentElement.clientWidth,
    );

test.describe("Signal Ledger Ticket Workbench V2", () => {
    test.use({ storageState: path.join(here, ".auth/staff.json") });

    test("opens from V2 list, renders conversation and returns to V2 list", async ({
        page,
    }) => {
        await page.goto("/tickets");
        await page.waitForLoadState("networkidle");
        const list = page.locator('main [data-ui="v2"]');
        await expect(list.locator("table")).toBeVisible();
        const ticket = list
            .locator('a[href^="/tickets/"]:not([href="/tickets/new"])')
            .first();
        await ticket.click();
        await page.waitForLoadState("networkidle");
        const workbench = page.locator('main [data-ui="v2"]');
        await expect(workbench.locator("h1")).toBeVisible();
        await expect(workbench.getByLabel(/conversation/i)).toBeVisible();
        await page.goBack();
        await expect(list.locator("table")).toBeVisible();
    });

    for (const width of [1440, 1024, 768, 375] as const) {
        test(`has no document overflow at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto("/tickets");
            await page.waitForLoadState("networkidle");
            await page
                .locator(
                    'main [data-ui="v2"] a[href^="/tickets/"]:not([href="/tickets/new"])',
                )
                .first()
                .click();
            await page.waitForLoadState("networkidle");
            await expect(page.locator('main [data-ui="v2"] h1')).toBeVisible();
            expect(await noOverflow(page)).toBe(true);
        });
    }

    for (const width of [1440, 768, 375] as const) {
        test(`${width}px Arabic workbench composition`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto("/tickets");
            await page.evaluate(() => localStorage.setItem("locale", "ar"));
            await page.reload();
            await page
                .locator(
                    'main [data-ui="v2"] a[href^="/tickets/"]:not([href="/tickets/new"])',
                )
                .first()
                .click();
            await page.waitForLoadState("networkidle");
            const workbench = page.locator('main [data-ui="v2"]');
            await expect(workbench).toHaveAttribute("dir", "rtl");
            await expect(workbench.locator("h1")).toBeVisible();
            await expect(workbench.locator("[data-workbench-conversation]")).toBeVisible();
            await expect(workbench.locator("[data-workbench-composer]")).toBeVisible();
            await expect(workbench.locator('[role="radiogroup"]')).toBeVisible();
            await expect(workbench.locator("aside")).toBeVisible();
            expect(await noOverflow(page)).toBe(true);
        });
    }
});
