import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { useGetTicketCategories } from "@/api/generated/ticketing/ticketing";
import type { ApiPage } from "@/api/http/envelope";
import { pickBilingual } from "../utils/bilingual";
import { statusLabelKey, priorityLabelKey } from "../utils/labels";
import type {
    TicketCategory,
    TicketLifecycleType,
    TicketPriority,
} from "../types";
import type { TicketFilterKey, TicketFilters } from "./useTicketListQuery";
import styles from "./TicketFilterBar.module.css";

const STATUS_OPTIONS: TicketLifecycleType[] = [
    "new",
    "open",
    "pending",
    "resolved",
    "closed",
    "spam",
];
const PRIORITY_OPTIONS: TicketPriority[] = ["low", "normal", "high", "urgent"];

const SEARCH_DEBOUNCE_MS = 300;

interface TicketFilterBarProps {
    allowedFilterKeys: readonly TicketFilterKey[];
    filters: TicketFilters;
    search?: string;
    departmentNames: Map<string, string>;
    onFilterChange: (key: TicketFilterKey, value: string | undefined) => void;
    onSearchChange: (value: string | undefined) => void;
    onClear: () => void;
}

export function TicketFilterBar({
    allowedFilterKeys,
    filters,
    search,
    departmentNames,
    onFilterChange,
    onSearchChange,
    onClear,
}: TicketFilterBarProps) {
    const { t, i18n } = useTranslation();
    const [searchDraft, setSearchDraft] = useState(search ?? "");

    useEffect(() => {
        setSearchDraft(search ?? "");
    }, [search]);

    useEffect(() => {
        const handle = setTimeout(() => {
            if (searchDraft !== (search ?? "")) {
                onSearchChange(searchDraft || undefined);
            }
            // eslint-disable-next-line react-hooks/exhaustive-deps
        }, SEARCH_DEBOUNCE_MS);
        return () => clearTimeout(handle);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [searchDraft]);

    const categoriesQuery = useGetTicketCategories(
        { per_page: 100 },
        { query: { enabled: allowedFilterKeys.includes("category") } },
    ) as unknown as { data?: ApiPage<TicketCategory> };

    const hasActiveFilters =
        Boolean(search) || Object.values(filters).some(Boolean);

    return (
        <div className={styles.root}>
            <div className={`${styles.field} ${styles.searchField}`}>
                <label htmlFor="ticket-search">
                    {t("tickets.filters.search_label")}
                </label>
                <input
                    id="ticket-search"
                    type="search"
                    value={searchDraft}
                    onChange={(event) => setSearchDraft(event.target.value)}
                    placeholder={t("tickets.filters.search_placeholder")}
                    className={styles.control}
                />
            </div>

            {allowedFilterKeys.includes("status") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-status">
                        {t("tickets.filters.status_label")}
                    </label>
                    <select
                        id="ticket-filter-status"
                        value={filters.status ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "status",
                                event.target.value || undefined,
                            )
                        }
                        className={styles.control}
                    >
                        <option value="">{t("tickets.filters.any")}</option>
                        {STATUS_OPTIONS.map((status) => (
                            <option key={status} value={status}>
                                {t(statusLabelKey(status))}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {allowedFilterKeys.includes("priority") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-priority">
                        {t("tickets.filters.priority_label")}
                    </label>
                    <select
                        id="ticket-filter-priority"
                        value={filters.priority ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "priority",
                                event.target.value || undefined,
                            )
                        }
                        className={styles.control}
                    >
                        <option value="">{t("tickets.filters.any")}</option>
                        {PRIORITY_OPTIONS.map((priority) => (
                            <option key={priority} value={priority}>
                                {t(priorityLabelKey(priority))}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {allowedFilterKeys.includes("department") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-department">
                        {t("tickets.filters.department_label")}
                    </label>
                    <select
                        id="ticket-filter-department"
                        value={filters.department ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "department",
                                event.target.value || undefined,
                            )
                        }
                        className={styles.control}
                    >
                        <option value="">{t("tickets.filters.any")}</option>
                        {[...departmentNames.entries()].map(([id, name]) => (
                            <option key={id} value={id}>
                                {name}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {allowedFilterKeys.includes("category") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-category">
                        {t("tickets.filters.category_label")}
                    </label>
                    <select
                        id="ticket-filter-category"
                        value={filters.category ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "category",
                                event.target.value || undefined,
                            )
                        }
                        className={styles.control}
                    >
                        <option value="">{t("tickets.filters.any")}</option>
                        {(categoriesQuery.data?.items ?? []).map((category) => (
                            <option key={category.id} value={category.id}>
                                {pickBilingual(category.name, i18n.language)}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {allowedFilterKeys.includes("assignee") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-assignee">
                        {t("tickets.filters.assignee_label")}
                    </label>
                    <input
                        id="ticket-filter-assignee"
                        type="text"
                        value={filters.assignee ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "assignee",
                                event.target.value || undefined,
                            )
                        }
                        placeholder={t("tickets.filters.uuid_placeholder")}
                        className={styles.control}
                    />
                </div>
            )}

            {allowedFilterKeys.includes("customer") && (
                <div className={styles.field}>
                    <label htmlFor="ticket-filter-customer">
                        {t("tickets.filters.customer_label")}
                    </label>
                    <input
                        id="ticket-filter-customer"
                        type="text"
                        value={filters.customer ?? ""}
                        onChange={(event) =>
                            onFilterChange(
                                "customer",
                                event.target.value || undefined,
                            )
                        }
                        placeholder={t("tickets.filters.uuid_placeholder")}
                        className={styles.control}
                    />
                </div>
            )}

            {hasActiveFilters && (
                <button
                    type="button"
                    onClick={onClear}
                    className={styles.clear}
                >
                    {t("tickets.filters.clear")}
                </button>
            )}
        </div>
    );
}
