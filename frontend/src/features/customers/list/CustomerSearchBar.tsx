import { useTranslation } from "react-i18next";
import type { CustomerStatus } from "../types";
import styles from "./CustomerSearchBar.module.css";

const STATUS_OPTIONS: CustomerStatus[] = ["active", "blocked", "anonymised"];

interface CustomerSearchBarProps {
    search: string;
    status?: CustomerStatus;
    onSearchChange: (value: string) => void;
    onStatusChange: (status: CustomerStatus | undefined) => void;
    onClear: () => void;
}

export function CustomerSearchBar({
    search,
    status,
    onSearchChange,
    onStatusChange,
    onClear,
}: CustomerSearchBarProps) {
    const { t } = useTranslation();
    const hasActiveFilters = Boolean(search) || Boolean(status);

    return (
        <div className={styles.root}>
            <div className={styles.field}>
                <label htmlFor="customer-search" className={styles.label}>
                    {t("customers.filters.search_label")}
                </label>
                <input
                    id="customer-search"
                    type="search"
                    value={search}
                    onChange={(event) => onSearchChange(event.target.value)}
                    placeholder={t("customers.filters.search_placeholder")}
                    className={styles.input}
                />
            </div>

            <div className={styles.field}>
                <label
                    htmlFor="customer-filter-status"
                    className={styles.label}
                >
                    {t("customers.filters.status_label")}
                </label>
                <select
                    id="customer-filter-status"
                    value={status ?? ""}
                    onChange={(event) =>
                        onStatusChange(
                            (event.target.value || undefined) as
                                CustomerStatus | undefined,
                        )
                    }
                    className={styles.select}
                >
                    <option value="">{t("customers.filters.any")}</option>
                    {STATUS_OPTIONS.map((option) => (
                        <option key={option} value={option}>
                            {t(`customers.status.${option}`)}
                        </option>
                    ))}
                </select>
            </div>

            {hasActiveFilters && (
                <button
                    type="button"
                    onClick={onClear}
                    className={styles.clear}
                >
                    {t("customers.filters.clear")}
                </button>
            )}
        </div>
    );
}
