import { useState } from "react";
import { useTranslation } from "react-i18next";
import { ActionGuard } from "@/shell/ActionGuard";
import { RequirePermission } from "@/auth/RequirePermission";
import { PERMISSIONS, TICKETS_VIEW_SCOPES } from "@/auth/permissions";
import { useGetDepartments } from "@/api/generated/organization/organization";
import type { ApiPage } from "@/api/http/envelope";
import {
    useTicketListQuery,
    type TicketQueueMode,
} from "@/features/tickets/list/useTicketListQuery";
import { useDepartmentNames } from "@/features/tickets/utils/useDepartmentNames";
import { TicketListTable } from "@/features/tickets/list/TicketListTable";
import { TicketFilterBar } from "@/features/tickets/list/TicketFilterBar";
import { SavedViewsBar } from "@/features/tickets/list/SavedViewsBar";
import { TicketBulkActions } from "@/features/tickets/list/TicketBulkActions";
import { Link } from "react-router-dom";
import {
    pickBilingual,
    type BilingualValue,
} from "@/features/tickets/utils/bilingual";
import { V2PortalBoundary } from "@/design-system/foundations/V2PortalBoundary";
import { Button } from "@/design-system/primitives/Button";
import { EmptyState } from "@/design-system/patterns/EmptyState";
import { ErrorState } from "@/design-system/patterns/ErrorState";
import { ForbiddenState } from "@/design-system/patterns/ForbiddenState";
import { LoadingState } from "@/design-system/patterns/LoadingState";
import styles from "./TicketsPageV2.module.css";

interface DepartmentOption {
    id: string;
    name: BilingualValue;
}

export function TicketsPage() {
    const { t, i18n } = useTranslation();
    const [mode, setMode] = useState<TicketQueueMode>("all");
    const [departmentId, setDepartmentId] = useState<string | undefined>(
        undefined,
    );
    const [selected, setSelected] = useState<Set<string>>(new Set());

    const {
        state,
        setState,
        setFilter,
        clearFilters,
        applyState,
        allowedFilterKeys,
        query,
    } = useTicketListQuery(mode, departmentId);
    const { byId: departmentNames } = useDepartmentNames();

    const departmentsQuery = useGetDepartments(
        { per_page: 100 },
        { query: { enabled: mode === "department" } },
    ) as unknown as { data?: ApiPage<DepartmentOption> };

    const toggleRow = (uuid: string) => {
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(uuid)) next.delete(uuid);
            else next.add(uuid);
            return next;
        });
    };

    const toggleAll = () => {
        const rows = query.data?.items ?? [];
        setSelected((prev) => {
            const allSelected =
                rows.length > 0 && rows.every((row) => prev.has(row.uuid));
            return allSelected
                ? new Set()
                : new Set(rows.map((row) => row.uuid));
        });
    };

    const handleModeChange = (nextMode: TicketQueueMode) => {
        setMode(nextMode);
        setSelected(new Set());
        if (nextMode !== "department") setDepartmentId(undefined);
    };

    const rtl = i18n.dir(i18n.language) === "rtl";
    const activeFilterCount =
        Object.values(state.filters).filter(Boolean).length +
        (state.search ? 1 : 0);
    return (
        <V2PortalBoundary dir={rtl ? "rtl" : "ltr"} lang={rtl ? "ar" : "en"}>
            <div className={styles.page}>
                <header className={styles.header}>
                    <h1>{t("pages.tickets.title")}</h1>
                    <RequirePermission permission={PERMISSIONS.TICKETS_CREATE}>
                        <Link className={styles.create} to="/tickets/new">
                            {t("pages.tickets.create_ticket")}
                        </Link>
                    </RequirePermission>
                </header>
                <div
                    className={styles.queueTabs}
                    role="tablist"
                    aria-label="Ticket queues"
                >
                    <button
                        type="button"
                        onClick={() => handleModeChange("all")}
                        className={
                            mode === "all" ? styles.queueActive : styles.queue
                        }
                    >
                        {t("tickets.mode.all")}
                    </button>
                    <ActionGuard permission={PERMISSIONS.TICKETS_QUEUE_VIEW}>
                        <button
                            type="button"
                            onClick={() => handleModeChange("mine")}
                            className={
                                mode === "mine"
                                    ? styles.queueActive
                                    : styles.queue
                            }
                        >
                            {t("tickets.mode.mine")}
                        </button>
                        <button
                            type="button"
                            onClick={() => handleModeChange("department")}
                            className={
                                mode === "department"
                                    ? styles.queueActive
                                    : styles.queue
                            }
                        >
                            {t("tickets.mode.department")}
                        </button>
                    </ActionGuard>
                </div>

                {mode === "department" && (
                    <div className={styles.departmentPicker}>
                        <label
                            htmlFor="queue-department"
                            className="me-2 text-sm font-medium text-gray-600"
                        >
                            {t("tickets.mode.department_select_label")}
                        </label>
                        <select
                            id="queue-department"
                            value={departmentId ?? ""}
                            onChange={(event) =>
                                setDepartmentId(event.target.value || undefined)
                            }
                            className={styles.select}
                        >
                            <option value="">
                                {t(
                                    "tickets.mode.department_select_placeholder",
                                )}
                            </option>
                            {(departmentsQuery.data?.items ?? []).map(
                                (department) => (
                                    <option
                                        key={department.id}
                                        value={department.id}
                                    >
                                        {pickBilingual(
                                            department.name,
                                            i18n.language,
                                        )}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>
                )}

                <RequirePermission
                    anyPermission={TICKETS_VIEW_SCOPES}
                    fallback={
                        <ForbiddenState
                            title={t("tickets.v2.forbidden_title")}
                        />
                    }
                >
                    <div className={styles.tools}>
                        <SavedViewsBar
                            currentSort={state.sort}
                            currentSearch={state.search}
                            currentFilters={state.filters}
                            onApply={(view) =>
                                applyState({
                                    sort: view.sort,
                                    search: view.search,
                                    filters: view.filters,
                                    perPage: state.perPage,
                                })
                            }
                        />
                        <TicketFilterBar
                            allowedFilterKeys={allowedFilterKeys}
                            filters={state.filters}
                            search={state.search}
                            departmentNames={departmentNames}
                            onFilterChange={setFilter}
                            onSearchChange={(value) =>
                                setState({ search: value })
                            }
                            onClear={clearFilters}
                        />
                        <p className={styles.resultMeta}>
                            {activeFilterCount
                                ? t("tickets.v2.active_filters", {
                                      count: activeFilterCount,
                                  })
                                : t("tickets.v2.all_tickets")}
                        </p>
                    </div>

                    <TicketBulkActions
                        selected={[...selected]}
                        onCleared={() => setSelected(new Set())}
                    />

                    <div className={styles.results}>
                        {query.isPending ? (
                            <LoadingState
                                title={t("tickets.v2.loading_title")}
                            />
                        ) : query.isError ? (
                            (query.error as { status?: number }).status ===
                            403 ? (
                                <ForbiddenState
                                    title={t("tickets.v2.forbidden_title")}
                                />
                            ) : (
                                <ErrorState
                                    title={t("tickets.v2.error_title")}
                                    action={
                                        <Button
                                            variant="secondary"
                                            onClick={() => query.refetch()}
                                        >
                                            {t("tickets.v2.retry")}
                                        </Button>
                                    }
                                />
                            )
                        ) : query.data?.items.length === 0 ? (
                            <EmptyState
                                title={t("tickets.list.empty_title")}
                                description={t(
                                    "tickets.list.empty_description",
                                )}
                                action={
                                    activeFilterCount ? (
                                        <Button
                                            variant="secondary"
                                            onClick={clearFilters}
                                        >
                                            {t("tickets.filters.clear")}
                                        </Button>
                                    ) : undefined
                                }
                            />
                        ) : query.data ? (
                            <>
                                <TicketListTable
                                    rows={query.data.items}
                                    sort={state.sort}
                                    onSortChange={(sort) => setState({ sort })}
                                    selected={selected}
                                    onToggleRow={toggleRow}
                                    onToggleAll={toggleAll}
                                    departmentNames={departmentNames}
                                />
                                <div className={styles.pagination}>
                                    <span>
                                        {t("tickets.list.pagination_summary", {
                                            page: query.data.meta.page,
                                            totalPages:
                                                query.data.meta.total_pages,
                                            total: query.data.meta.total,
                                        })}
                                    </span>
                                    <div className="flex gap-2">
                                        <button
                                            type="button"
                                            disabled={query.data.meta.page <= 1}
                                            onClick={() =>
                                                setState({
                                                    page:
                                                        query.data.meta.page -
                                                        1,
                                                })
                                            }
                                            className={styles.paginationButton}
                                        >
                                            {t("tickets.list.previous_page")}
                                        </button>
                                        <button
                                            type="button"
                                            disabled={
                                                query.data.meta.page >=
                                                query.data.meta.total_pages
                                            }
                                            onClick={() =>
                                                setState({
                                                    page:
                                                        query.data.meta.page +
                                                        1,
                                                })
                                            }
                                            className={styles.paginationButton}
                                        >
                                            {t("tickets.list.next_page")}
                                        </button>
                                    </div>
                                </div>
                            </>
                        ) : null}
                    </div>
                </RequirePermission>
            </div>
        </V2PortalBoundary>
    );
}
