import { describe, expect, it, vi } from "vitest";
import { configureAxe } from "vitest-axe";
import { render } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { I18nextProvider } from "react-i18next";
import { TicketDetailPage } from "../TicketDetailPage";
import { AuthContext } from "@/auth/AuthProvider";
import i18n from "@/i18n";

const ticket = {
    id: "ticket-uuid",
    reference: "TCK-100",
    customer_id: null,
    department_id: null,
    category_id: null,
    assignee_id: null,
    subject: "Arabic and English subject",
    body: "Body",
    status: {
        uuid: "open",
        key: "open",
        name: "Open",
        lifecycle_type: "open",
        stops_sla_clock: false,
    },
    priority: "normal",
    custom_fields: null,
    available_transitions: [],
    merged_into_id: null,
    parent_ticket_id: null,
    version: 1,
    assigned_at: null,
    reopen_deadline_at: null,
    reopened_count: 0,
    is_watched: false,
    created_at: null,
    updated_at: null,
    sla: null,
};

vi.mock("@/api/generated/ticketing/ticketing", () => ({
    useGetTicket: () => ({ data: ticket, isPending: false, isError: false }),
    useGetTicketMessages: () => ({
        data: {
            items: [
                {
                    uuid: "m1",
                    author_type: "customer",
                    is_internal: false,
                    body: "Help needed",
                    created_at: "2026-01-01T00:00:00Z",
                },
            ],
            meta: { total: 1 },
        },
        isLoading: false,
        isError: false,
    }),
    getGetTicketMessagesQueryKey: () => ["messages"],
}));
vi.mock("@/api/generated/customers/customers", () => ({
    useGetCustomer: () => ({ data: { name: "Ada" } }),
}));
vi.mock("@/features/tickets/detail/TicketStatusControl", () => ({
    TicketStatusControl: () => <button>Status control</button>,
}));
vi.mock("@/features/tickets/detail/TicketAssignmentControl", () => ({
    TicketAssignmentControl: () => <button>Assignment control</button>,
}));
vi.mock("@/features/tickets/detail/TicketPropertiesPanel", () => ({
    TicketPropertiesPanel: () => <p>Priority control</p>,
}));
vi.mock("@/features/tickets/detail/TicketSlaPanel", () => ({
    TicketSlaPanel: () => <p>SLA</p>,
}));
vi.mock("@/features/tickets/detail/TicketWatchersPanel", () => ({
    TicketWatchersPanel: () => <p>Watchers</p>,
}));
vi.mock("@/features/tickets/detail/TicketTasksPanel", () => ({
    TicketTasksPanel: () => <p>Tasks</p>,
}));
vi.mock("@/features/tickets/detail/CustomerContextPanel", () => ({
    CustomerContextPanel: () => <p>Requester</p>,
}));
vi.mock("@/features/tickets/detail/TicketHistoryPanel", () => ({
    TicketHistoryPanel: () => <p>History</p>,
}));
vi.mock("@/features/tickets/detail/TicketAiPanel", () => ({
    TicketAiPanel: () => null,
}));
vi.mock("@/features/tickets/api/wire", async () => {
    const actual = await vi.importActual<
        typeof import("@/features/tickets/api/wire")
    >("@/features/tickets/api/wire");
    return {
        ...actual,
        useWatchTicket: () => ({ mutate: vi.fn(), isPending: false }),
        useUnwatchTicket: () => ({ mutate: vi.fn(), isPending: false }),
        useSendTicketMessage: () => ({ mutate: vi.fn(), isPending: false }),
    };
});
vi.mock("@/features/tickets/useTicketMutation", () => ({
    useTicketMutation: () => ({
        mutate: vi.fn(),
        isPending: false,
        conflict: null,
        fieldErrors: {},
        reloadLatest: vi.fn(),
    }),
}));
vi.mock("@/shared/attachments/AttachmentUploader", () => ({
    AttachmentUploader: () => <button>Attach file</button>,
}));
vi.mock("@/features/workspace/quickReplies/QuickReplyPicker", () => ({
    QuickReplyPicker: () => null,
}));
vi.mock("@/features/workspace/quickReplies/useInsertQuickReply", () => ({
    useInsertQuickReply: () => ({ insert: vi.fn() }),
}));

const axe = configureAxe({
    rules: { "color-contrast": { enabled: false }, region: { enabled: false } },
});
function renderPage(lang = "en") {
    i18n.changeLanguage(lang);
    const query = new QueryClient({
        defaultOptions: { queries: { retry: false } },
    });
    return render(
        <QueryClientProvider client={query}>
            <I18nextProvider i18n={i18n as never}>
                <AuthContext.Provider
                    value={{
                        status: "authenticated",
                        user: {
                            id: "agent",
                            email: "a@test",
                            name: "Agent",
                            locale: lang,
                            available_locales: ["en", "ar"],
                            permission_keys: [
                                "ticket.message.send",
                                "ticket.message.internal_write",
                                "workspace.ticket.watchers.view",
                            ],
                            primary_branch_id: null,
                            department_ids: [],
                        },
                        permissions: [
                            "ticket.message.send",
                            "ticket.message.internal_write",
                            "workspace.ticket.watchers.view",
                        ],
                        login: async () => {},
                        completeTwoFactor: async () => {},
                        logout: async () => {},
                        reload: async () => {},
                    }}
                >
                    <MemoryRouter initialEntries={["/tickets/ticket-uuid"]}>
                        <Routes>
                            <Route
                                path="/tickets/:ticketId"
                                element={<TicketDetailPage />}
                            />
                        </Routes>
                    </MemoryRouter>
                </AuthContext.Provider>
            </I18nextProvider>
        </QueryClientProvider>,
    );
}
describe("TicketDetailPage V2", () => {
    it("is accessible in LTR", async () => {
        const { container } = renderPage();
        expect((await axe(container)).violations).toEqual([]);
    });
    it("is accessible in RTL", async () => {
        const { container } = renderPage("ar");
        expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute(
            "dir",
            "rtl",
        );
        expect((await axe(container)).violations).toEqual([]);
    });
});
