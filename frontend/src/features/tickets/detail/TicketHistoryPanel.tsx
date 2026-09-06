import { useState } from "react";
import { useTranslation } from "react-i18next";
import type { UseQueryResult } from "@tanstack/react-query";
import {
    useGetTicketHistory,
    useGetTicketLinks,
} from "@/api/generated/ticketing/ticketing";
import type { ApiPage } from "@/api/http/envelope";
import { AsyncBoundary } from "@/shell/AsyncBoundary";
import { EmptyState } from "@/shell/states/EmptyState";
import { ActionGuard } from "@/shell/ActionGuard";
import { PERMISSIONS } from "@/auth/permissions";
import type { TicketEvent, TicketLink } from "../types";
import styles from "./TicketInspectorV2.module.css";

interface TicketHistoryPanelProps {
    ticketId: string;
}

export function TicketHistoryPanel({ ticketId }: TicketHistoryPanelProps) {
    const { t } = useTranslation();
    const [isOpen, setIsOpen] = useState(false);

    return (
        <div className={styles.stack}>
            <button
                type="button"
                onClick={() => setIsOpen((open) => !open)}
                className={styles.button}
            >
                {t("tickets.history.heading")}
                <span>{isOpen ? "▲" : "▼"}</span>
            </button>

            {isOpen && (
                <ActionGuard permission={PERMISSIONS.TICKETS_HISTORY_VIEW}>
                    <div className={styles.stack}>
                        <EventsList ticketId={ticketId} />
                        <LinksList ticketId={ticketId} />
                    </div>
                </ActionGuard>
            )}
        </div>
    );
}

function EventsList({ ticketId }: { ticketId: string }) {
    const { t } = useTranslation();
    const query = useGetTicketHistory(
        ticketId,
        { per_page: 25 },
        { query: { enabled: !!ticketId } },
    ) as unknown as UseQueryResult<ApiPage<TicketEvent>, unknown>;

    return (
        <div className={styles.stack}>
            <h3 className={styles.label}>
                {t("tickets.history.events_heading")}
            </h3>
            <AsyncBoundary
                query={query}
                isEmpty={(page) => page.items.length === 0}
                empty={<EmptyState title={t("tickets.history.events_empty")} />}
            >
                {(page) => (
                    <ul className={styles.eventList}>
                        {page.items.map((event) => (
                            <li key={event.uuid} className={styles.event}>
                                <time>
                                    {event.occurred_at
                                        ? new Date(
                                              event.occurred_at,
                                          ).toLocaleString()
                                        : "—"}
                                </time>{" "}
                                {t(`tickets.history.event_type.${event.type}`, {
                                    defaultValue: event.type ?? "",
                                })}
                            </li>
                        ))}
                    </ul>
                )}
            </AsyncBoundary>
        </div>
    );
}

function LinksList({ ticketId }: { ticketId: string }) {
    const { t } = useTranslation();
    const query = useGetTicketLinks(
        ticketId,
        { per_page: 25 },
        { query: { enabled: !!ticketId } },
    ) as unknown as UseQueryResult<ApiPage<TicketLink>, unknown>;

    return (
        <ActionGuard permission={PERMISSIONS.TICKETS_LINK}>
            <div className={styles.stack}>
                <h3 className={styles.label}>
                    {t("tickets.history.links_heading")}
                </h3>
                <AsyncBoundary
                    query={query}
                    isEmpty={(page) => page.items.length === 0}
                    empty={
                        <EmptyState title={t("tickets.history.links_empty")} />
                    }
                >
                    {(page) => (
                        <ul className={styles.eventList}>
                            {page.items.map((link) => (
                                <li key={link.uuid} className={styles.event}>
                                    {t(
                                        `tickets.history.link_relation.${link.relation}`,
                                    )}{" "}
                                    ·{" "}
                                    {new Date(
                                        link.created_at,
                                    ).toLocaleDateString()}
                                </li>
                            ))}
                        </ul>
                    )}
                </AsyncBoundary>
            </div>
        </ActionGuard>
    );
}
