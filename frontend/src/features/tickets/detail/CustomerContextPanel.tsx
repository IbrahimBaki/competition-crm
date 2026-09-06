import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import type { UseQueryResult } from "@tanstack/react-query";
import {
    useGetCustomer,
    useGetCustomerTimeline,
} from "@/api/generated/customers/customers";
import type { GetCustomerTimelineParams } from "@/api/generated/model/getCustomerTimelineParams";
import { AsyncBoundary } from "@/shell/AsyncBoundary";
import { LoadingState } from "@/shell/states/LoadingState";
import { ActionGuard } from "@/shell/ActionGuard";
import { PERMISSIONS } from "@/auth/permissions";
import { usePermissions } from "@/auth/usePermissions";
import { CustomerStatusBadge } from "@/features/customers/detail/CustomerStatusBadge";
import styles from "./TicketInspectorV2.module.css";

interface CustomerSummary {
    uuid: string;
    name: string;
    status: string;
}

interface TimelineEntry {
    id: string;
    source: string;
    type: string;
    occurred_at: string;
}

interface CustomerContextPanelProps {
    customerId: string | null;
}

// Read-only in this story; deep link opens the customer screen separately.
export function CustomerContextPanel({
    customerId,
}: CustomerContextPanelProps) {
    const { t } = useTranslation();

    if (!customerId) {
        return (
            <p className={styles.empty}>{t("tickets.customer_context.none")}</p>
        );
    }

    return (
        <ActionGuard permission={PERMISSIONS.CUSTOMERS_VIEW}>
            <CustomerContextPanelContent customerId={customerId} />
        </ActionGuard>
    );
}

function CustomerContextPanelContent({ customerId }: { customerId: string }) {
    const { t } = useTranslation();

    const { can } = usePermissions();

    const customerQuery = useGetCustomer(
        customerId,
    ) as unknown as UseQueryResult<CustomerSummary, unknown>;

    // The real endpoint paginates by `limit`/`before` (see
    // CustomerTimelineController::index), not page/per_page as
    // getCustomerTimelineParams.ts declares.
    const timelineQuery = useGetCustomerTimeline(
        customerId,
        { limit: 10 } as unknown as GetCustomerTimelineParams,
        {
            query: { enabled: can(PERMISSIONS.CUSTOMERS_TIMELINE_VIEW) },
        },
    ) as unknown as UseQueryResult<TimelineEntry[], unknown>;

    return (
        <div className={styles.stack}>
            <AsyncBoundary query={customerQuery}>
                {(customer) => (
                    <div className={styles.stack}>
                        <p>{customer.name}</p>
                        <CustomerStatusBadge
                            status={customer.status}
                            label={t(
                                `tickets.customer_context.status.${customer.status}`,
                            )}
                        />
                        <Link
                            to={`/customers/${customer.uuid}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={styles.button}
                        >
                            {t("tickets.customer_context.open_profile")}
                        </Link>
                    </div>
                )}
            </AsyncBoundary>

            <ActionGuard permission={PERMISSIONS.CUSTOMERS_TIMELINE_VIEW}>
                <div className={styles.stack}>
                    <h3 className={styles.label}>
                        {t("tickets.customer_context.timeline_heading")}
                    </h3>
                    <AsyncBoundary
                        query={timelineQuery}
                        isEmpty={(entries) => entries.length === 0}
                        loading={
                            <LoadingState
                                rows={2}
                                label={t("tickets.conversation.loading")}
                            />
                        }
                        empty={
                            <p className={styles.empty}>
                                {t("tickets.customer_context.timeline_empty")}
                            </p>
                        }
                    >
                        {(entries) => (
                            <ul className={styles.eventList}>
                                {entries.map((entry) => (
                                    <li key={entry.id} className={styles.event}>
                                        <time>
                                            {new Date(
                                                entry.occurred_at,
                                            ).toLocaleDateString()}
                                        </time>{" "}
                                        {t(
                                            `tickets.customer_context.timeline_type.${entry.type}`,
                                            { defaultValue: entry.type },
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </AsyncBoundary>
                </div>
            </ActionGuard>
        </div>
    );
}
