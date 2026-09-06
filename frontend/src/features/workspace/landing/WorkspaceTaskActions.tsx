import { useState } from "react";
import { useTranslation } from "react-i18next";
import { Button } from "@/design-system/primitives/Button";
import { Input } from "@/design-system/primitives/Input";
import {
    Dialog,
    DialogClose,
    DialogContent,
} from "@/design-system/composites/Dialog";
import { localDateTimeToWire } from "../api/wire";
import { useAgentTaskMutations } from "../tasks/useAgentTaskMutations";
import type { AgentTask } from "../types";
import styles from "./WorkspaceV2.module.css";

export function WorkspaceTaskActions({ task }: { task: AgentTask }) {
    const { t } = useTranslation();
    const [rescheduleOpen, setRescheduleOpen] = useState(false);
    const [dueAt, setDueAt] = useState("");
    const mutations = useAgentTaskMutations();
    const canComplete = task.state === "open" || task.state === "in_progress";
    const canReschedule = task.state !== "done" && task.state !== "cancelled";
    const saveReschedule = () => {
        const value = localDateTimeToWire(dueAt);
        if (value) mutations.update(task.uuid, { dueAt: value });
    };

    return (
        <div className={styles.taskActions}>
            {canComplete ? (
                <Button
                    size="compact"
                    variant="secondary"
                    loading={mutations.changeStateState.isPending}
                    onClick={() => mutations.changeState(task.uuid, "done")}
                >
                    {t("workspace.task.action_complete")}
                </Button>
            ) : null}
            {canReschedule ? (
                <Dialog open={rescheduleOpen} onOpenChange={setRescheduleOpen}>
                    <Button
                        size="compact"
                        variant="ghost"
                        onClick={() => setRescheduleOpen(true)}
                    >
                        {t("workspace.task.action_reschedule")}
                    </Button>
                    <DialogContent
                        title={t("workspace.v2.task_reschedule.title")}
                        description={task.title}
                    >
                        <div className={styles.taskDialogBody}>
                            <label htmlFor={`task-due-${task.uuid}`}>
                                {t("workspace.task_form.due_at_label")}
                            </label>
                            <Input
                                id={`task-due-${task.uuid}`}
                                type="datetime-local"
                                value={dueAt}
                                onChange={(event) =>
                                    setDueAt(event.target.value)
                                }
                            />
                            <div className={styles.dialogActions}>
                                <DialogClose asChild>
                                    <Button variant="ghost">
                                        {t("workspace.task_form.cancel")}
                                    </Button>
                                </DialogClose>
                                <Button
                                    loading={mutations.updateState.isPending}
                                    disabled={!dueAt}
                                    onClick={saveReschedule}
                                >
                                    {t("workspace.task.action_save")}
                                </Button>
                            </div>
                            {mutations.updateState.isError ? (
                                <p className={styles.actionError} role="alert">
                                    {t("workspace.task.error_generic")}
                                </p>
                            ) : null}
                        </div>
                    </DialogContent>
                </Dialog>
            ) : null}
            {mutations.changeStateState.isError ? (
                <p className={styles.actionError} role="alert">
                    {t("workspace.task.error_generic")}
                </p>
            ) : null}
        </div>
    );
}
