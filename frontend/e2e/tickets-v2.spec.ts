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

test.describe("Signal Ledger Tickets List V2", () => {
    test.use({ storageState: path.join(here, ".auth/staff.json") });

    test("keeps the list V2, preserves URL search, and isolates V1 ticket detail", async ({
        page,
    }) => {
        await page.goto("/tickets");
        await page.waitForLoadState("networkidle");
        const boundary = page.locator('main [data-ui="v2"]');
        await expect(boundary.getByRole("heading", { level: 1 })).toBeVisible();
        await expect(boundary.locator("table")).toBeVisible();

        const search = boundary.getByLabel(/search/i);
        await search.fill("seed");
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/q=seed/);

        const record = boundary
            .locator('a[href^="/tickets/"]:not([href="/tickets/new"])')
            .first();
        await expect(record).toBeVisible();
        await record.click();
        await expect(page).toHaveURL(/\/tickets\/[0-9a-f-]{36}/);
        await expect(page.locator('main [data-ui="v2"]')).toHaveCount(0);
        await page.goBack();
        await expect(boundary.getByRole("heading", { level: 1 })).toBeVisible();
    });

    for (const width of [1440, 1024, 768, 375] as const) {
        test(`has no document overflow at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto("/tickets");
            await page.waitForLoadState("networkidle");
            await expect(page.locator('main [data-ui="v2"]')).toBeVisible();
            expect(await noOverflow(page)).toBe(true);
        });
    }

    for (const width of [1440, 768, 375] as const) {
        test(`${width}px Arabic tickets composition remains bounded`, async ({
            page,
        }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto("/tickets");
            await page.evaluate(() => localStorage.setItem("locale", "ar"));
            await page.reload();
            await page.waitForLoadState("networkidle");
            const boundary = page.locator('main [data-ui="v2"]');
            await expect(boundary).toHaveAttribute("dir", "rtl");
            await expect(
                boundary.getByRole("heading", { level: 1 }),
            ).toBeVisible();
            expect(await noOverflow(page)).toBe(true);
        });
    }
});
