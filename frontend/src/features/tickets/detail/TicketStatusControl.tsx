import { useState } from "react";
import { useTranslation } from "react-i18next";
import { ActionGuard } from "@/shell/ActionGuard";
import { PERMISSIONS } from "@/auth/permissions";
import { useChangeTicketStatus } from "../api/wire";
import { useTicketMutation } from "../useTicketMutation";
import { ConflictBanner } from "./ConflictBanner";
import { pickBilingual } from "../utils/bilingual";
import type { TicketDetail } from "../types";
import styles from "./TicketInspectorV2.module.css";

interface TicketStatusControlProps {
    ticket: TicketDetail;
}

export function TicketStatusControl({ ticket }: TicketStatusControlProps) {
    const { t, i18n } = useTranslation();
    const [selectedUuid, setSelectedUuid] = useState("");
    const [reason, setReason] = useState("");
    const [showReasonFor, setShowReasonFor] = useState<string | null>(null);

    const mutation = useTicketMutation(useChangeTicketStatus, ticket.id, {
        onSuccess: () => {
            setSelectedUuid("");
            setReason("");
            setShowReasonFor(null);
        },
    });

    const transitions = ticket.available_transitions;
    const selectedTransition = transitions.find(
        (transition) => transition.uuid === selectedUuid,
    );

    const handleChange = (uuid: string) => {
        setSelectedUuid(uuid);
        const transition = transitions.find((item) => item.uuid === uuid);
        if (transition?.requires_reason) {
            setShowReasonFor(uuid);
        } else {
            setShowReasonFor(null);
        }
    };

    const submit = () => {
        if (!selectedUuid) return;
        if (selectedTransition?.requires_reason && !reason.trim()) return;
        mutation.mutate({
            ticket: ticket.id,
            statusId: selectedUuid,
            reason: reason.trim() || undefined,
        });
    };

    if (transitions.length === 0) {
        return (
            <p className={styles.empty}>{t("tickets.status.no_transitions")}</p>
        );
    }

    return (
        <ActionGuard permission={PERMISSIONS.TICKETS_STATUS_CHANGE}>
            <div className={styles.stack}>
                {mutation.conflict && (
                    <ConflictBanner
                        error={mutation.conflict}
                        onReload={mutation.reloadLatest}
                    />
                )}

                <select
                    value={selectedUuid}
                    onChange={(event) => handleChange(event.target.value)}
                    disabled={mutation.isPending}
                    className={styles.control}
                >
                    <option value="">{t("tickets.status.choose")}</option>
                    {transitions.map((transition) => (
                        <option key={transition.uuid} value={transition.uuid}>
                            {pickBilingual(transition.name, i18n.language)}
                        </option>
                    ))}
                </select>

                {showReasonFor && (
                    <textarea
                        value={reason}
                        onChange={(event) => setReason(event.target.value)}
                        placeholder={t("tickets.status.reason_placeholder")}
                        rows={2}
                        className={`${styles.control} ${styles.textarea}`}
                    />
                )}

                <button
                    type="button"
                    onClick={submit}
                    disabled={
                        !selectedUuid ||
                        (Boolean(showReasonFor) && !reason.trim()) ||
                        mutation.isPending
                    }
                    className={`${styles.button} ${styles.buttonPrimary}`}
                >
                    {t("tickets.status.apply")}
                </button>
            </div>
        </ActionGuard>
    );
}
