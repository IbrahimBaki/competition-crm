import { useState, type ReactNode } from "react";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useAuth } from "@/auth/AuthProvider";
import { PERMISSIONS } from "@/auth/permissions";
import { usePermissions } from "@/auth/usePermissions";
import { Badge } from "@/design-system/primitives/Badge";
import { Button } from "@/design-system/primitives/Button";
import { EmptyState } from "@/design-system/patterns/EmptyState";
import { ErrorState } from "@/design-system/patterns/ErrorState";
import { LoadingState } from "@/design-system/patterns/LoadingState";
import { ForbiddenState } from "@/design-system/patterns/ForbiddenState";
import { icons } from "@/design-system/foundations/icons";
import { useMyQueueQuery } from "../queues/useMyQueueQuery";
import { useDepartmentQueueQuery } from "../queues/useDepartmentQueueQuery";
import { useSlaRiskTickets } from "../queues/useSlaRiskTickets";
import { useAgentTaskListQuery } from "../tasks/useAgentTaskListQuery";
import { useNotificationsQuery } from "../notifications/useNotificationsQuery";
import { TaskFormDialog } from "../tasks/TaskFormDialog";
import { WorkspaceTaskActions } from "./WorkspaceTaskActions";
import type { TicketListRow, TicketPriority } from "../../tickets/types";
import type { AgentTask, WorkspaceNotification } from "../types";
import styles from "./WorkspaceV2.module.css";

type BadgeTone = "neutral" | "info" | "success" | "warning" | "danger";
const formatDate = (value: string | null, locale: string) =>
    value && !Number.isNaN(new Date(value).getTime())
        ? new Intl.DateTimeFormat(locale, {
              dateStyle: "medium",
              timeStyle: "short",
          }).format(new Date(value))
        : null;
const ticketTone = (priority: TicketPriority, status: string): BadgeTone =>
    priority === "urgent"
        ? "danger"
        : priority === "high" || status === "pending"
          ? "warning"
          : status === "resolved" || status === "closed"
            ? "success"
            : status === "new" || status === "open"
              ? "info"
              : "neutral";

function Section({
    title,
    description,
    action,
    children,
    className,
}: {
    title: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section
            className={[styles.section, className].filter(Boolean).join(" ")}
            aria-label={title}
        >
            <header className={styles.sectionHeader}>
                <div>
                    <h2>{title}</h2>
                    {description ? <p>{description}</p> : null}
                </div>
                {action}
            </header>
            {children}
        </section>
    );
}
function TicketRows({ rows }: { rows: TicketListRow[] }) {
    const { t } = useTranslation();
    return (
        <ul className={styles.ledger}>
            {rows.map((ticket) => (
                <li key={ticket.uuid}>
                    <Link
                        className={styles.ledgerLink}
                        to={`/tickets/${ticket.uuid}`}
                    >
                        <span className={styles.record}>
                            <span className="ds-numeric ds-bidi-value">
                                {ticket.reference}
                            </span>
                            <strong>{ticket.subject}</strong>
                        </span>
                        <span className={styles.rowMeta}>
                            <Badge
                                tone={ticketTone(
                                    ticket.priority,
                                    ticket.status,
                                )}
                            >
                                {t(`tickets.status.${ticket.status}`)}
                            </Badge>
                            {ticket.priority !== "normal" ? (
                                <Badge
                                    tone={
                                        ticket.priority === "urgent"
                                            ? "danger"
                                            : ticket.priority === "high"
                                              ? "warning"
                                              : "neutral"
                                    }
                                >
                                    {t(
                                        `workspace.v2.priority.${ticket.priority}`,
                                    )}
                                </Badge>
                            ) : null}
                        </span>
                    </Link>
                </li>
            ))}
        </ul>
    );
}
function TaskRows({ rows, locale }: { rows: AgentTask[]; locale: string }) {
    const { t } = useTranslation();
    return (
        <ul className={styles.ledger}>
            {rows.map((task) => (
                <li key={task.uuid} className={styles.taskRow}>
                    <span className={styles.record}>
                        <strong>{task.title}</strong>
                        {task.ticket ? (
                            <span className="ds-numeric ds-bidi-value">
                                {task.ticket.reference}
                            </span>
                        ) : null}
                    </span>
                    <span className={styles.rowMeta}>
                        <Badge
                            tone={
                                task.isOverdue
                                    ? "danger"
                                    : task.state === "done"
                                      ? "success"
                                      : task.state === "in_progress"
                                        ? "info"
                                        : "neutral"
                            }
                        >
                            {task.isOverdue
                                ? t("workspace.task.overdue_badge")
                                : t(`workspace.v2.task_state.${task.state}`)}
                        </Badge>
                        {task.dueAt ? (
                            <span className="ds-numeric">
                                {formatDate(task.dueAt, locale)}
                            </span>
                        ) : null}
                        <WorkspaceTaskActions task={task} />
                    </span>
                </li>
            ))}
        </ul>
    );
}
function ChangesRows({
    rows,
    locale,
}: {
    rows: WorkspaceNotification[];
    locale: string;
}) {
    const { t } = useTranslation();
    return (
        <ul className={styles.ledger}>
            {rows.map((item) => (
                <li key={item.uuid} className={styles.changeRow}>
                    <span className={styles.record}>
                        <strong>{item.subject || item.eventType}</strong>
                        {item.body ? <span>{item.body}</span> : null}
                    </span>
                    <span className={styles.rowMeta}>
                        <Badge tone={item.readAt ? "neutral" : "info"}>
                            {item.readAt
                                ? t("workspace.v2.read")
                                : t("workspace.v2.new")}
                        </Badge>
                        <time className="ds-numeric">
                            {formatDate(item.createdAt, locale)}
                        </time>
                    </span>
                </li>
            ))}
        </ul>
    );
}
function QueryState({
    loading,
    error,
    retry,
    empty,
    children,
}: {
    loading: boolean;
    error: boolean;
    retry: () => void;
    empty: ReactNode;
    children: ReactNode;
}) {
    const { t } = useTranslation();
    if (loading) return <LoadingState title={t("workspace.v2.loading")} />;
    if (error)
        return (
            <ErrorState
                title={t("workspace.v2.error_title")}
                description={t("workspace.v2.error_description")}
                action={
                    <Button variant="secondary" size="compact" onClick={retry}>
                        {t("workspace.v2.retry")}
                    </Button>
                }
            />
        );
    return <>{children || empty}</>;
}

export function WorkspaceV2() {
    const { t, i18n } = useTranslation();
    const { user } = useAuth();
    const { can } = usePermissions();
    const [taskFormOpen, setTaskFormOpen] = useState(false);
    const locale = i18n.language === "ar" ? "ar-EG" : "en-US";
    const canTasks = can(PERMISSIONS.WORKSPACE_TASKS_VIEW_OWN);
    const canNotifications = can(PERMISSIONS.NOTIFICATIONS_VIEW_OWN);
    const mine = useMyQueueQuery(10);
    const departmentId = user?.department_ids[0];
    const department = useDepartmentQueueQuery(
        can(PERMISSIONS.TICKETS_QUEUE_VIEW) ? departmentId : undefined,
        10,
    );
    const tasks = useAgentTaskListQuery(
        { overdue: true, ownerId: user?.id, sort: "due_at" },
        canTasks,
    );
    const notifications = useNotificationsQuery(10, canNotifications);
    const slaRisk = useSlaRiskTickets(10);
    return (
        <div className={styles.page}>
            <header className={styles.pageHeader}>
                <h1>{t("workspace.title")}</h1>
            </header>
            <section
                className={styles.attention}
                aria-labelledby="workspace-attention-title"
            >
                <div className={styles.attentionHeading}>
                    <h2 id="workspace-attention-title">
                        {t("workspace.v2.attention.title")}
                    </h2>
                    <p>{t("workspace.v2.attention.description")}</p>
                </div>
                <div className={styles.metrics}>
                    <Link to="/tickets?mode=mine">
                        <span>{t("workspace.v2.metric.assigned")}</span>
                        <strong className="ds-numeric">
                            {mine.data?.meta.total ?? "—"}
                        </strong>
                    </Link>
                    {canTasks ? (
                        <a href="#workspace-risk">
                            <span>{t("workspace.v2.metric.overdue")}</span>
                            <strong className="ds-numeric">
                                {String(tasks.data?.meta.total ?? "—")}
                            </strong>
                        </a>
                    ) : null}
                    {can(PERMISSIONS.TICKETS_QUEUE_VIEW) ? (
                        <Link to="/tickets?mode=department">
                            <span>{t("workspace.v2.metric.queue")}</span>
                            <strong className="ds-numeric">
                                {department.data?.meta.total ?? "—"}
                            </strong>
                        </Link>
                    ) : null}
                </div>
            </section>
            <div className={styles.desktopGrid}>
                <Section
                    className={styles.myWork}
                    title={t("workspace.v2.my_work.title")}
                    description={t("workspace.v2.my_work.description")}
                    action={
                        <Link
                            className={styles.textAction}
                            to="/tickets?mode=mine"
                        >
                            {t("workspace.panel.view_all")}
                        </Link>
                    }
                >
                    <QueryState
                        loading={mine.isLoading}
                        error={mine.isError}
                        retry={() => mine.refetch()}
                        empty={
                            <EmptyState
                                title={t(
                                    "workspace.panel.my_tickets.empty_title",
                                )}
                                description={t(
                                    "workspace.panel.my_tickets.empty_description",
                                )}
                            />
                        }
                    >
                        {mine.data?.items.length ? (
                            <TicketRows rows={mine.data.items} />
                        ) : null}
                    </QueryState>
                </Section>
                <div className={styles.sideColumn}>
                    <Section
                        className={styles.risk}
                        title={t("workspace.v2.risk.title")}
                        description={t("workspace.v2.risk.description")}
                    >
                        <div id="workspace-risk">
                            {!canTasks ? (
                                <ForbiddenState
                                    title={t("workspace.v2.permission_title")}
                                    description={t(
                                        "workspace.v2.risk.permission_description",
                                    )}
                                />
                            ) : (
                                <>
                                    <QueryState
                                        loading={tasks.isLoading}
                                        error={tasks.isError}
                                        retry={() => tasks.refetch()}
                                        empty={
                                            <EmptyState
                                                title={t(
                                                    "workspace.panel.overdue_tasks.empty_title",
                                                )}
                                            />
                                        }
                                    >
                                        {tasks.data?.items.length ? (
                                            <TaskRows
                                                rows={
                                                    tasks.data
                                                        .items as AgentTask[]
                                                }
                                                locale={locale}
                                            />
                                        ) : null}
                                    </QueryState>
                                    {slaRisk.available &&
                                    slaRisk.items.length > 0 ? (
                                        <div className={styles.slaRisk}>
                                            <Badge tone="warning">
                                                {t(
                                                    "workspace.panel.sla_risk.badge",
                                                )}
                                            </Badge>
                                            <span className="ds-numeric">
                                                {slaRisk.items.length}
                                            </span>
                                        </div>
                                    ) : null}
                                </>
                            )}
                        </div>
                    </Section>
                    <Section
                        title={t("workspace.v2.changes.title")}
                        description={t("workspace.v2.changes.description")}
                    >
                        {!canNotifications ? (
                            <ForbiddenState
                                title={t("workspace.v2.permission_title")}
                                description={t(
                                    "workspace.v2.changes.permission_description",
                                )}
                            />
                        ) : (
                            <QueryState
                                loading={notifications.query.isLoading}
                                error={notifications.query.isError}
                                retry={() => notifications.query.refetch()}
                                empty={
                                    <EmptyState
                                        title={t("workspace.v2.changes.empty")}
                                    />
                                }
                            >
                                {notifications.query.data?.items.length ? (
                                    <ChangesRows
                                        rows={notifications.query.data.items}
                                        locale={locale}
                                    />
                                ) : null}
                            </QueryState>
                        )}
                    </Section>
                </div>
            </div>
            {can(PERMISSIONS.TICKETS_QUEUE_VIEW) ? (
                <Section
                    title={t("workspace.panel.department_queue.title")}
                    description={
                        departmentId
                            ? t("workspace.v2.queue.description")
                            : t(
                                  "workspace.panel.department_queue.no_department_description",
                              )
                    }
                    action={
                        departmentId ? (
                            <Link
                                className={styles.textAction}
                                to="/tickets?mode=department"
                            >
                                {t("workspace.panel.view_all")}
                            </Link>
                        ) : undefined
                    }
                >
                    {!departmentId ? (
                        <EmptyState
                            title={t(
                                "workspace.panel.department_queue.no_department_title",
                            )}
                        />
                    ) : (
                        <QueryState
                            loading={department.isLoading}
                            error={department.isError}
                            retry={() => department.refetch()}
                            empty={
                                <EmptyState
                                    title={t(
                                        "workspace.panel.department_queue.empty_title",
                                    )}
                                    description={t(
                                        "workspace.panel.department_queue.empty_description",
                                    )}
                                />
                            }
                        >
                            {department.data?.items.length ? (
                                <TicketRows rows={department.data.items} />
                            ) : null}
                        </QueryState>
                    )}
                </Section>
            ) : null}
            {can(PERMISSIONS.WORKSPACE_TASKS_CREATE) ? (
                <div className={styles.taskAction}>
                    <Button
                        size="compact"
                        variant="secondary"
                        leadingIcon={icons.Check}
                        onClick={() => setTaskFormOpen(true)}
                    >
                        {t("workspace.task.action_add")}
                    </Button>
                </div>
            ) : null}
            <TaskFormDialog
                open={taskFormOpen}
                onClose={() => setTaskFormOpen(false)}
            />
        </div>
    );
}
