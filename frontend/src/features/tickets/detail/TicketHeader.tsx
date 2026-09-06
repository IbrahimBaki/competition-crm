import { useTranslation } from "react-i18next";
import { useAuth } from "@/auth/AuthProvider";
import { ActionGuard } from "@/shell/ActionGuard";
import { PERMISSIONS } from "@/auth/permissions";
import { useWatchTicket, useUnwatchTicket } from "../api/wire";
import { useTicketMutation } from "../useTicketMutation";
import { priorityLabelKey } from "../utils/labels";
import { pickBilingual } from "../utils/bilingual";
import type { TicketDetail } from "../types";
import { Badge } from "@/design-system/primitives/Badge";
import styles from "./TicketHeader.module.css";

interface TicketHeaderProps {
    ticket: TicketDetail;
}

export function TicketHeader({ ticket }: TicketHeaderProps) {
    const { t, i18n } = useTranslation();
    const { user } = useAuth();

    const watch = useTicketMutation(useWatchTicket, ticket.id);
    const unwatch = useTicketMutation(useUnwatchTicket, ticket.id);

    const toggleWatch = () => {
        if (ticket.is_watched) {
            unwatch.mutate({ ticket: ticket.id, userId: user?.id ?? "" });
        } else {
            watch.mutate({ ticket: ticket.id });
        }
    };

    return (
        <header className={styles.root}>
            <div className={styles.top}>
                <div>
                    <p className={`${styles.reference} ds-bidi-value`}>
                        {ticket.reference}
                    </p>
                    <h1 className={styles.subject}>{ticket.subject}</h1>
                </div>

                <ActionGuard
                    permission={PERMISSIONS.WORKSPACE_TICKET_WATCHERS_VIEW}
                >
                    <button
                        type="button"
                        onClick={toggleWatch}
                        disabled={watch.isPending || unwatch.isPending}
                        className={styles.watch}
                    >
                        {ticket.is_watched
                            ? t("tickets.detail.unwatch")
                            : t("tickets.detail.watch")}
                    </button>
                </ActionGuard>
            </div>

            <div className={styles.states}>
                {ticket.status && (
                    <Badge
                        tone={
                            ticket.status.lifecycle_type === "spam"
                                ? "danger"
                                : ticket.status.lifecycle_type === "pending"
                                  ? "warning"
                                  : ticket.status.lifecycle_type ===
                                          "resolved" ||
                                      ticket.status.lifecycle_type === "closed"
                                    ? "success"
                                    : "info"
                        }
                    >
                        {pickBilingual(ticket.status.name, i18n.language)}
                    </Badge>
                )}
                {ticket.priority && (
                    <Badge
                        tone={
                            ticket.priority === "urgent"
                                ? "danger"
                                : ticket.priority === "high"
                                  ? "warning"
                                  : ticket.priority === "normal"
                                    ? "info"
                                    : "neutral"
                        }
                    >
                        {t(priorityLabelKey(ticket.priority))}
                    </Badge>
                )}
                {ticket.assignee_id ? (
                    <span className="text-xs text-gray-500">
                        {t("tickets.detail.assigned_to")}:{" "}
                        <code>{ticket.assignee_id}</code>
                    </span>
                ) : (
                    <span className="text-xs text-gray-400">
                        {t("tickets.detail.unassigned")}
                    </span>
                )}
            </div>
        </header>
    );
}
