import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import type { CustomerListRow } from "../types";
import type { CustomerSortField } from "./useCustomerListQuery";
import { CustomerStatusBadge } from "../detail/CustomerStatusBadge";
import styles from "./CustomerListTable.module.css";

interface SortableColumn {
    field: CustomerSortField;
    labelKey: string;
}

// Only name/status/created_at are sortable server-side — see
// CustomerController::index()'s CollectionQuerySpec::withSorts().
const COLUMNS: SortableColumn[] = [
    { field: "name", labelKey: "customers.list.column.name" },
    { field: "status", labelKey: "customers.list.column.status" },
    { field: "created_at", labelKey: "customers.list.column.created_at" },
];

interface CustomerListTableProps {
    rows: CustomerListRow[];
    sort?: string;
    onSortChange: (sort: string) => void;
}

// Columns are deliberately limited to what CustomerController::index()
// actually returns (raw Eloquent columns, no CustomerResource wrapping —
// see .squad/gaps/34-481.md #7). Primary contact / company account /
// service tier are only available on the detail screen.
export function CustomerListTable({
    rows,
    sort,
    onSortChange,
}: CustomerListTableProps) {
    const { t, i18n } = useTranslation();
    const dateFormatter = new Intl.DateTimeFormat(i18n.language, {
        dateStyle: "medium",
    });

    const sortField = sort?.replace(/^-/, "");
    const sortDescending = sort?.startsWith("-") ?? false;

    const handleHeaderClick = (field: CustomerSortField) => {
        if (sortField === field) {
            onSortChange(sortDescending ? field : `-${field}`);
        } else {
            onSortChange(`-${field}`);
        }
    };

    return (
        <div className={styles.wrap}>
            <table className={styles.table}>
                <thead>
                    <tr>
                        {COLUMNS.map((column) => (
                            <th
                                key={column.field}
                                scope="col"
                                aria-sort={
                                    sortField === column.field
                                        ? sortDescending
                                            ? "descending"
                                            : "ascending"
                                        : "none"
                                }
                            >
                                <button
                                    className={styles.sort}
                                    type="button"
                                    onClick={() =>
                                        handleHeaderClick(column.field)
                                    }
                                >
                                    {t(column.labelKey)}
                                    {sortField === column.field &&
                                        (sortDescending ? " ↓" : " ↑")}
                                </button>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr key={row.uuid}>
                            <td data-label={t("customers.list.column.name")}>
                                <Link
                                    to={`/customers/${row.uuid}`}
                                    className={styles.link}
                                >
                                    {row.name}
                                </Link>
                                {row.mergedIntoCustomerId !== null && (
                                    <span>
                                        {t("customers.list.merged_badge")}
                                    </span>
                                )}
                            </td>
                            <td data-label={t("customers.list.column.status")}>
                                <CustomerStatusBadge
                                    status={row.status}
                                    label={t(`customers.status.${row.status}`)}
                                />
                            </td>
                            <td
                                className={styles.date}
                                data-label={t(
                                    "customers.list.column.created_at",
                                )}
                            >
                                {dateFormatter.format(new Date(row.createdAt))}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
