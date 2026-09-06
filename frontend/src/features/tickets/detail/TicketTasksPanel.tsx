import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AsyncBoundary } from "@/shell/AsyncBoundary";
import { EmptyState } from "@/shell/states/EmptyState";
import { useAgentTaskListQuery } from "@/features/workspace/tasks/useAgentTaskListQuery";
import { TaskFormDialog } from "@/features/workspace/tasks/TaskFormDialog";
import { TaskStateControl } from "@/features/workspace/tasks/TaskStateControl";
import { RescheduleTaskControl } from "@/features/workspace/tasks/RescheduleTaskControl";
import type { AgentTask } from "@/features/workspace/types";
import styles from "./TicketInspectorV2.module.css";

interface TicketTasksPanelProps {
    ticket: { uuid: string; reference: string };
}

export function TicketTasksPanel({ ticket }: TicketTasksPanelProps) {
    const { t } = useTranslation();
    const [formOpen, setFormOpen] = useState(false);

    const query = useAgentTaskListQuery({
        ticketId: ticket.uuid,
        sort: "-due_at",
    });

    return (
        <div className={styles.stack}>
            <div className={styles.actions}>
                <button
                    type="button"
                    onClick={() => setFormOpen(true)}
                    className={`${styles.button} ${styles.buttonPrimary}`}
                >
                    {t("workspace.ticket_tasks.add")}
                </button>
            </div>

            <AsyncBoundary
                query={query}
                isEmpty={(data) => data.items.length === 0}
                empty={<EmptyState title={t("workspace.ticket_tasks.empty")} />}
            >
                {(data) => (
                    <WorkbenchTaskLedger tasks={data.items as AgentTask[]} />
                )}
            </AsyncBoundary>

            <TaskFormDialog
                open={formOpen}
                onClose={() => setFormOpen(false)}
                ticket={ticket}
            />
        </div>
    );
}

function WorkbenchTaskLedger({ tasks }: { tasks: AgentTask[] }) {
    const { t } = useTranslation();
    return (
        <ul className={styles.eventList}>
            {tasks.map((task) => (
                <li className={styles.event} key={task.uuid}>
                    <strong>{task.title}</strong>
                    {task.isOverdue && (
                        <span className={styles.label}>
                            {" "}
                            · {t("workspace.task.overdue_badge")}
                        </span>
                    )}
                    <div className={styles.muted}>
                        {task.dueAt
                            ? t("workspace.task.due_label", {
                                  date: new Date(task.dueAt).toLocaleString(),
                              })
                            : "—"}
                    </div>
                    <div className={styles.actions}>
                        <TaskStateControl task={task} />
                        <RescheduleTaskControl task={task} />
                    </div>
                </li>
            ))}
        </ul>
    );
}
