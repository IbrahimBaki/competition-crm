import { MemoryRouter } from "react-router-dom";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { configureAxe } from "vitest-axe";
import { afterEach, describe, expect, it, vi } from "vitest";
import i18n from "@/i18n";
import { V2PortalBoundary } from "@/design-system/foundations/V2PortalBoundary";
import { TicketListTable } from "../TicketListTable";

const rows = [
    {
        id: 1,
        uuid: "ticket-1",
        reference: "SUP-101",
        subject: "Arabic-safe record identity",
        status: "open" as const,
        priority: "high" as const,
        department_id: "dept-1",
        customer_id: null,
        ticket_category_id: null,
        assigned_user_id: null,
        custom_fields: null,
        version: 1,
        created_at: "2026-09-01T09:00:00Z",
        updated_at: "2026-09-02T10:00:00Z",
    },
];
const axe = configureAxe({
    rules: { "color-contrast": { enabled: false }, region: { enabled: false } },
});
function renderTable(rtl = false) {
    const selected = new Set<string>();
    return render(
        <MemoryRouter>
            <V2PortalBoundary
                dir={rtl ? "rtl" : "ltr"}
                lang={rtl ? "ar" : "en"}
            >
                <TicketListTable
                    rows={rows}
                    selected={selected}
                    onToggleRow={vi.fn()}
                    onToggleAll={vi.fn()}
                    departmentNames={new Map([["dept-1", "Support"]])}
                    onSortChange={vi.fn()}
                />
            </V2PortalBoundary>
        </MemoryRouter>,
    );
}
describe("TicketListTable V2", () => {
    afterEach(async () => { await i18n.changeLanguage("en"); });
    it("keeps semantic table identity, explicit record links and selection controls", async () => {
        const { container } = renderTable();
        expect(screen.getByRole("table")).toBeVisible();
        expect(screen.getByRole("link", { name: "SUP-101" })).toHaveAttribute(
            "href",
            "/tickets/ticket-1",
        );
        expect(screen.getByRole("checkbox", { name: "Select ticket SUP-101" })).toBeVisible();
        expect((await axe(container)).violations).toEqual([]);
    });
    it("is keyboard-operable and RTL-safe", async () => {
        await i18n.changeLanguage("ar");
        const user = userEvent.setup();
        const { container } = renderTable(true);
        await user.tab();
        expect(screen.getByRole("checkbox", { name: /تحديد كل التذاكر/i })).toHaveFocus();
        expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute(
            "dir",
            "rtl",
        );
        expect(screen.getByRole("link", { name: "SUP-101" })).toHaveClass(
            "ds-bidi-value",
        );
        expect((await axe(container)).violations).toEqual([]);
    });
});
