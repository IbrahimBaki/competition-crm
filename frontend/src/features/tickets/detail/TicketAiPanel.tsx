import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { apiRequest } from "@/api/http/mutator";
import { Button } from "@/design-system/primitives/Button";
import { usePermissions } from "@/auth/usePermissions";
import { PERMISSIONS } from "@/auth/permissions";
import styles from "./TicketInspectorV2.module.css";

export function TicketAiPanel({ ticketId }: { ticketId: string }) {
    const { can } = usePermissions();
    const [result, setResult] = useState<unknown>(null);
    const mutation = useMutation({
        mutationFn: (feature: string | undefined) =>
            apiRequest({
                url: `/tickets/${ticketId}/ai/${feature ?? ""}`,
                method: "POST",
                data: {},
                headers: { "Idempotency-Key": crypto.randomUUID() },
            }),
        onSuccess: setResult,
    });
    if (!can(PERMISSIONS.AI_ASSISTANCE_USE)) return null;
    return (
        <div className={styles.stack}>
            <p className={styles.muted}>
                Generated content requires staff review before it can be used.
            </p>
            <div className={styles.actions}>
                {[
                    ["summary", "Summarize"],
                    ["suggested-reply", "Suggest reply"],
                    ["classify", "Classify"],
                    ["suggested-articles", "Find articles"],
                ].map(([feature, label]) => (
                    <Button
                        key={feature}
                        variant="secondary"
                        disabled={
                            mutation.isPending && mutation.variables === feature
                        }
                        onClick={() => mutation.mutate(feature)}
                    >
                        {label}
                    </Button>
                ))}
            </div>
            {mutation.isError && (
                <p className={styles.empty} role="alert">
                    AI assistance is unavailable. Try again later.
                </p>
            )}
            {result !== null && (
                <pre className={styles.event}>
                    {JSON.stringify(result, null, 2)}
                </pre>
            )}
        </div>
    );
}
