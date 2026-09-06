import { useTranslation } from "react-i18next";
import type { TicketSlaBlock, TicketSlaPosition } from "../types";
import styles from "./TicketInspectorV2.module.css";

interface TicketSlaPanelProps {
    sla: TicketSlaBlock | null;
}

// Display-only: every value here (state, target/elapsed/remaining minutes,
// due_at) comes verbatim from the server. Formatting is allowed; computing a
// remaining time, percentage or breach state from a timestamp client-side is
// forbidden (see app/Domains/Sla/Services/SlaClockService.php — that logic
// must never be mirrored in the browser). See the guard test
// no-sla-recalculation.test.ts.
export function TicketSlaPanel({ sla }: TicketSlaPanelProps) {
    const { t } = useTranslation();

    if (!sla || (!sla.first_response && !sla.resolution)) {
        return <p className={styles.empty}>{t("tickets.sla.none")}</p>;
    }

    return (
        <div className={styles.stack}>
            {sla.first_response && (
                <SlaRow
                    labelKey="tickets.sla.first_response"
                    position={sla.first_response}
                />
            )}
            {sla.resolution && (
                <SlaRow
                    labelKey="tickets.sla.resolution"
                    position={sla.resolution}
                />
            )}
        </div>
    );
}

function SlaRow({
    labelKey,
    position,
}: {
    labelKey: string;
    position: TicketSlaPosition;
}) {
    const { t, i18n } = useTranslation();
    const dueAt = position.due_at
        ? new Intl.DateTimeFormat(i18n.language, {
              dateStyle: "medium",
              timeStyle: "short",
          }).format(new Date(position.due_at))
        : null;

    return (
        <div className={styles.field}>
            <span className={styles.label}>
                {t(labelKey)} · {t(`tickets.sla.state.${position.state}`)}
            </span>
            <dl className={styles.ledger}>
                {dueAt && (
                    <div className={styles.ledgerRow}>
                        <dt>{t("tickets.sla.due_at")}</dt>
                        <dd>{dueAt}</dd>
                    </div>
                )}
                <div className={styles.ledgerRow}>
                    <dt>{t("tickets.sla.target")}</dt>
                    <dd>
                        {t("tickets.sla.minutes", {
                            count: position.target_minutes,
                        })}
                    </dd>
                </div>
                <div className={styles.ledgerRow}>
                    <dt>{t("tickets.sla.elapsed")}</dt>
                    <dd>
                        {t("tickets.sla.minutes", {
                            count: position.elapsed_minutes,
                        })}
                    </dd>
                </div>
                <div className={styles.ledgerRow}>
                    <dt>{t("tickets.sla.remaining")}</dt>
                    <dd>
                        {t("tickets.sla.minutes", {
                            count: position.remaining_minutes,
                        })}
                    </dd>
                </div>
            </dl>
            {position.warning_fired && (
                <p className={styles.label}>{t("tickets.sla.warning_fired")}</p>
            )}
        </div>
    );
}
