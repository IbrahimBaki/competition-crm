import { useTranslation } from "react-i18next";
import { useQueryClient, type UseQueryResult } from "@tanstack/react-query";
import {
    useGetTicketWatchers,
    getGetTicketWatchersQueryKey,
} from "@/api/generated/ticketing/ticketing";
import { useWatchTicket, useUnwatchTicket } from "../api/wire";
import { useAuth } from "@/auth/AuthProvider";
import { usePermissions } from "@/auth/usePermissions";
import { PERMISSIONS } from "@/auth/permissions";
import { AsyncBoundary } from "@/shell/AsyncBoundary";
import { EmptyState } from "@/shell/states/EmptyState";
import type { TicketWatchersResponse } from "../types";
import styles from "./TicketInspectorV2.module.css";

interface TicketWatchersPanelProps {
    ticket: string;
}

export function TicketWatchersPanel({ ticket }: TicketWatchersPanelProps) {
    const { t } = useTranslation();
    const { user } = useAuth();
    const { can } = usePermissions();
    const queryClient = useQueryClient();

    const query = useGetTicketWatchers(ticket) as unknown as UseQueryResult<
        TicketWatchersResponse,
        unknown
    >;

    const invalidate = () =>
        queryClient.invalidateQueries({
            queryKey: getGetTicketWatchersQueryKey(ticket),
        });

    // POST watchers is idempotent server-side (a duplicate add is treated as
    // success) — invalidate/refetch on success either way, never surface a
    // conflict as an error.
    const watch = useWatchTicket({
        onSuccess: invalidate,
        onError: invalidate,
    });
    const unwatch = useUnwatchTicket({ onSuccess: invalidate });

    if (!can(PERMISSIONS.WORKSPACE_TICKET_WATCHERS_VIEW)) {
        return null;
    }

    const isWatching =
        query.data?.watchers.some((watcher) => watcher.uuid === user?.id) ??
        false;

    return (
        <div className={styles.stack}>
            <div className={styles.actions}>
                <button
                    type="button"
                    onClick={() =>
                        isWatching
                            ? unwatch.mutate({ ticket, userId: user?.id ?? "" })
                            : watch.mutate({ ticket })
                    }
                    disabled={watch.isPending || unwatch.isPending}
                    className={styles.button}
                >
                    {isWatching
                        ? t("tickets.detail.unwatch")
                        : t("tickets.detail.watch")}
                </button>
            </div>

            <AsyncBoundary
                query={query}
                isEmpty={(data) => data.watchers.length === 0}
                empty={<EmptyState title={t("tickets.watchers.empty")} />}
            >
                {(data) => (
                    <ul className={styles.eventList}>
                        {data.watchers.map((watcher) => (
                            <li key={watcher.uuid} className={styles.event}>
                                <span>{watcher.name}</span>{" "}
                                <button
                                    type="button"
                                    onClick={() =>
                                        unwatch.mutate({
                                            ticket,
                                            userId: watcher.uuid,
                                        })
                                    }
                                    className={styles.button}
                                >
                                    {t("tickets.watchers.remove")}
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
            </AsyncBoundary>
        </div>
    );
}
