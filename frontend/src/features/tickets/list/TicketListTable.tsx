import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import type { TicketListRow } from "../types";
import type { TicketSortField } from "./useTicketListQuery";
import { statusLabelKey, priorityLabelKey } from "../utils/labels";
import { Badge } from "@/design-system/primitives/Badge";
import styles from "./TicketListTable.module.css";

interface SortableColumn {
    field: TicketSortField;
    labelKey: string;
}

const COLUMNS: SortableColumn[] = [
    { field: "reference", labelKey: "tickets.list.column.reference" },
    { field: "status", labelKey: "tickets.list.column.status" },
    { field: "priority", labelKey: "tickets.list.column.priority" },
    { field: "updated_at", labelKey: "tickets.list.column.updated_at" },
];

interface TicketListTableProps {
    rows: TicketListRow[];
    sort?: string;
    onSortChange: (sort: string) => void;
    selected: Set<string>;
    onToggleRow: (uuid: string) => void;
    onToggleAll: () => void;
    departmentNames: Map<string, string>;
}

export function TicketListTable({
    rows,
    sort,
    onSortChange,
    selected,
    onToggleRow,
    onToggleAll,
    departmentNames,
}: TicketListTableProps) {
    const { t, i18n } = useTranslation();
    const dateFormatter = new Intl.DateTimeFormat(i18n.language, {
        dateStyle: "medium",
        timeStyle: "short",
    });

    const sortField = sort?.replace(/^-/, "");
    const sortDescending = sort?.startsWith("-") ?? false;

    const handleHeaderClick = (field: TicketSortField) => {
        if (sortField === field) {
            onSortChange(sortDescending ? field : `-${field}`);
        } else {
            onSortChange(`-${field}`);
        }
    };

    const allSelected =
        rows.length > 0 && rows.every((row) => selected.has(row.uuid));

    return (
        <div className={styles.wrap}>
            <table className={styles.table}>
                <thead>
                    <tr>
                        <th className={styles.selection}>
                            <input
                                type="checkbox"
                                aria-label={t("tickets.list.select_all")}
                                checked={allSelected}
                                onChange={onToggleAll}
                            />
                        </th>
                        <th scope="col" className={styles.subject}>
                            {t("tickets.list.column.subject")}
                        </th>
                        {COLUMNS.map((column) => (
                            <th
                                key={column.field}
                                scope="col"
                                className={styles.sortable}
                                aria-sort={
                                    sortField === column.field
                                        ? sortDescending
                                            ? "descending"
                                            : "ascending"
                                        : "none"
                                }
                            >
                                <button
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
                        <th scope="col" className={styles.department}>
                            {t("tickets.list.column.department")}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr
                            key={row.uuid}
                            className={
                                selected.has(row.uuid)
                                    ? styles.selected
                                    : undefined
                            }
                        >
                            <td className={styles.selection}>
                                <input
                                    type="checkbox"
                                    aria-label={t("tickets.list.select_row", {
                                        reference: row.reference,
                                    })}
                                    checked={selected.has(row.uuid)}
                                    onChange={() => onToggleRow(row.uuid)}
                                />
                            </td>
                            <td
                                className={styles.subject}
                                data-label={t("tickets.list.column.subject")}
                            >
                                <Link
                                    to={`/tickets/${row.uuid}`}
                                    className={styles.subjectLink}
                                >
                                    {row.subject}
                                </Link>
                            </td>
                            <td
                                className={styles.reference}
                                data-label={t("tickets.list.column.reference")}
                            >
                                <Link
                                    to={`/tickets/${row.uuid}`}
                                    className="ds-bidi-value"
                                >
                                    {row.reference}
                                </Link>
                            </td>
                            <td data-label={t("tickets.list.column.status")}>
                                <Badge
                                    tone={
                                        row.status === "spam"
                                            ? "danger"
                                            : row.status === "pending"
                                              ? "warning"
                                              : row.status === "resolved" ||
                                                  row.status === "closed"
                                                ? "success"
                                                : "info"
                                    }
                                >
                                    {t(statusLabelKey(row.status))}
                                </Badge>
                            </td>
                            <td data-label={t("tickets.list.column.priority")}>
                                <Badge
                                    tone={
                                        row.priority === "urgent"
                                            ? "danger"
                                            : row.priority === "high"
                                              ? "warning"
                                              : row.priority === "normal"
                                                ? "info"
                                                : "neutral"
                                    }
                                >
                                    {t(priorityLabelKey(row.priority))}
                                </Badge>
                            </td>
                            <td
                                className={styles.updated}
                                data-label={t("tickets.list.column.updated_at")}
                            >
                                {dateFormatter.format(new Date(row.updated_at))}
                            </td>
                            <td
                                className={styles.department}
                                data-label={t("tickets.list.column.department")}
                            >
                                {row.department_id
                                    ? (departmentNames.get(row.department_id) ??
                                      "—")
                                    : "—"}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
