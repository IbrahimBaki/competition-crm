import { useTranslation } from "react-i18next";
import { ActionGuard } from "@/shell/ActionGuard";
import { PERMISSIONS } from "@/auth/permissions";
import { useUpdateTicketPriority } from "../api/wire";
import { useTicketMutation } from "../useTicketMutation";
import { ConflictBanner } from "./ConflictBanner";
import { priorityLabelKey } from "../utils/labels";
import type { TicketDetail, TicketPriority } from "../types";
import styles from "./TicketInspectorV2.module.css";

const PRIORITY_OPTIONS: TicketPriority[] = ["low", "normal", "high", "urgent"];

interface TicketPropertiesPanelProps {
    ticket: TicketDetail;
}

// Only `priority` has a real update path (UpdateTicketRequest::rules()).
// Category / department / tags / custom fields are shown read-only: no
// endpoint accepts changes to them today (see api/wire.ts header comment).
export function TicketPropertiesPanel({ ticket }: TicketPropertiesPanelProps) {
    const { t } = useTranslation();
    const mutation = useTicketMutation(useUpdateTicketPriority, ticket.id);

    return (
        <div className={styles.stack}>
            {mutation.conflict && (
                <ConflictBanner
                    error={mutation.conflict}
                    onReload={mutation.reloadLatest}
                />
            )}

            <div className={styles.field}>
                <label htmlFor="ticket-priority" className={styles.label}>
                    {t("tickets.properties.priority_label")}
                </label>
                <ActionGuard permission={PERMISSIONS.TICKETS_UPDATE}>
                    <select
                        id="ticket-priority"
                        value={ticket.priority ?? ""}
                        disabled={mutation.isPending}
                        onChange={(event) =>
                            mutation.mutate({
                                ticket: ticket.id,
                                priority: event.target.value,
                            })
                        }
                        className={styles.control}
                    >
                        {PRIORITY_OPTIONS.map((priority) => (
                            <option key={priority} value={priority}>
                                {t(priorityLabelKey(priority))}
                            </option>
                        ))}
                    </select>
                </ActionGuard>
            </div>

            <dl className={styles.ledger}>
                <div className={styles.ledgerRow}>
                    <dt>{t("tickets.properties.category_label")}</dt>
                    <dd>
                        {ticket.category_id
                            ? t("tickets.properties.read_only")
                            : "—"}
                    </dd>
                </div>
                <div className={styles.ledgerRow}>
                    <dt>{t("tickets.properties.department_label")}</dt>
                    <dd>
                        {ticket.department_id
                            ? t("tickets.properties.read_only")
                            : "—"}
                    </dd>
                </div>
            </dl>

            {ticket.custom_fields &&
                Object.keys(ticket.custom_fields).length > 0 && (
                    <div className={styles.field}>
                        <p className={styles.label}>
                            {t("tickets.properties.custom_fields_label")}
                        </p>
                        <dl className={styles.ledger}>
                            {Object.entries(ticket.custom_fields).map(
                                ([key, value]) => (
                                    <div className={styles.ledgerRow} key={key}>
                                        <dt>{key}</dt>
                                        <dd>{String(value)}</dd>
                                    </div>
                                ),
                            )}
                        </dl>
                    </div>
                )}
        </div>
    );
}
