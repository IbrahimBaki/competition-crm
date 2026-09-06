import { useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import type { UseQueryResult } from "@tanstack/react-query";
import { useGetTicket } from "@/api/generated/ticketing/ticketing";
import type { TicketDetail } from "@/features/tickets/types";
import { TicketHeader } from "@/features/tickets/detail/TicketHeader";
import { TicketConversation } from "@/features/tickets/detail/TicketConversation";
import { TicketComposer } from "@/features/tickets/detail/TicketComposer";
import { TicketPropertiesPanel } from "@/features/tickets/detail/TicketPropertiesPanel";
import { TicketStatusControl } from "@/features/tickets/detail/TicketStatusControl";
import { TicketAssignmentControl } from "@/features/tickets/detail/TicketAssignmentControl";
import { TicketSlaPanel } from "@/features/tickets/detail/TicketSlaPanel";
import { CustomerContextPanel } from "@/features/tickets/detail/CustomerContextPanel";
import { TicketHistoryPanel } from "@/features/tickets/detail/TicketHistoryPanel";
import { TicketTasksPanel } from "@/features/tickets/detail/TicketTasksPanel";
import { TicketWatchersPanel } from "@/features/tickets/detail/TicketWatchersPanel";
import { TicketAiPanel } from "@/features/tickets/detail/TicketAiPanel";
import { V2PortalBoundary } from "@/design-system/foundations/V2PortalBoundary";
import { LoadingState } from "@/design-system/patterns/LoadingState";
import { ErrorState } from "@/design-system/patterns/ErrorState";
import { ForbiddenState } from "@/design-system/patterns/ForbiddenState";
import { Button } from "@/design-system/primitives/Button";
import styles from "./TicketDetailPageV2.module.css";

export function TicketDetailPage() {
    const { ticketId } = useParams<{ ticketId: string }>();
    const { t, i18n } = useTranslation();

    const query = useGetTicket(ticketId ?? "", {
        query: { enabled: !!ticketId },
    }) as unknown as UseQueryResult<TicketDetail, unknown>;

    if (!ticketId) return <ErrorState title={t("error.not_found")} />;
    const rtl = i18n.dir(i18n.language) === "rtl";

    return (
        <V2PortalBoundary dir={rtl ? "rtl" : "ltr"} lang={rtl ? "ar" : "en"}>
            <div className={styles.page}>
                {query.isPending ? (
                    <LoadingState
                        title={t("tickets.detail.conversation_heading")}
                    />
                ) : query.isError ? (
                    (query.error as { status?: number }).status === 404 ? (
                        <ErrorState title={t("error.not_found")} />
                    ) : (query.error as { status?: number }).status === 403 ? (
                        <ForbiddenState
                            title={t("tickets.v2.forbidden_title")}
                        />
                    ) : (
                        <ErrorState
                            title={t("tickets.detail.conversation_heading")}
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
                ) : query.data ? (
                    <div className={styles.workbench}>
                        <main className={styles.main}>
                            <TicketHeader ticket={query.data} />
                            <TicketConversation ticketId={query.data.id} />
                            <TicketComposer ticket={query.data} />
                        </main>

                        <aside className={styles.inspector}>
                            <section className={styles.inspectorGroup}>
                                <h2>{t("tickets.detail.status_heading")}</h2>
                                <TicketStatusControl ticket={query.data} />
                                <TicketPropertiesPanel ticket={query.data} />
                                <TicketSlaPanel sla={query.data.sla} />
                            </section>
                            <section className={styles.inspectorGroup}>
                                <h2>
                                    {t("tickets.detail.assignment_heading")}
                                </h2>
                                <TicketAssignmentControl ticket={query.data} />
                            </section>
                            <section className={styles.inspectorGroup}>
                                <h2>{t("tickets.customer_context.heading")}</h2>
                                <CustomerContextPanel
                                    customerId={query.data.customer_id}
                                />
                            </section>
                            <section className={styles.inspectorGroup}>
                                <h2>
                                    {t("tickets.detail.properties_heading")}
                                </h2>
                                <TicketWatchersPanel ticket={query.data.id} />
                                <TicketTasksPanel
                                    ticket={{
                                        uuid: query.data.id,
                                        reference: query.data.reference,
                                    }}
                                />
                            </section>
                            <section className={styles.inspectorGroup}>
                                <h2>{t("tickets.history.heading")}</h2>
                                <TicketHistoryPanel ticketId={query.data.id} />
                            </section>
                            <section className={styles.inspectorGroup}>
                                <h2>AI</h2>
                                <TicketAiPanel ticketId={query.data.id} />
                            </section>
                        </aside>
                    </div>
                ) : null}
            </div>
        </V2PortalBoundary>
    );
}
