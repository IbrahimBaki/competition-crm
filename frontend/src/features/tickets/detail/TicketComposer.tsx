import { useRef, useState, type FormEvent } from "react";
import { useTranslation } from "react-i18next";
import { useQueryClient } from "@tanstack/react-query";
import {
    useGetTicketMessages,
    getGetTicketMessagesQueryKey,
} from "@/api/generated/ticketing/ticketing";
import type { GetTicketMessagesParams } from "@/api/generated/model/getTicketMessagesParams";
import { useGetCustomer } from "@/api/generated/customers/customers";
import { ActionGuard } from "@/shell/ActionGuard";
import { usePermissions } from "@/auth/usePermissions";
import { PERMISSIONS } from "@/auth/permissions";
import { useSendTicketMessage, newIdempotencyKey } from "../api/wire";
import { useTicketMutation } from "../useTicketMutation";
import { ConflictBanner } from "./ConflictBanner";
import {
    AttachmentUploader,
    type UploadedAttachment,
} from "@/shared/attachments/AttachmentUploader";
import { QuickReplyPicker } from "@/features/workspace/quickReplies/QuickReplyPicker";
import { useInsertQuickReply } from "@/features/workspace/quickReplies/useInsertQuickReply";
import type { TicketDetail, MessageChannel } from "../types";
import styles from "./TicketComposer.module.css";

type ComposerMode = "public" | "internal";

interface TicketComposerProps {
    ticket: TicketDetail;
}

export function TicketComposer({ ticket }: TicketComposerProps) {
    const { t, i18n } = useTranslation();
    const { can } = usePermissions();
    const queryClient = useQueryClient();
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const [mode, setMode] = useState<ComposerMode | null>(null);
    const [body, setBody] = useState("");
    const [attachments, setAttachments] = useState<UploadedAttachment[]>([]);
    const [idempotencyKey, setIdempotencyKey] = useState(() =>
        newIdempotencyKey(),
    );
    const [pickerOpen, setPickerOpen] = useState(false);
    const [mentionsInput, setMentionsInput] = useState("");

    const isReadOnly =
        Boolean(ticket.merged_into_id) ||
        ticket.status?.lifecycle_type === "spam";

    const send = useTicketMutation(useSendTicketMessage, ticket.id, {
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: getGetTicketMessagesQueryKey(ticket.id),
            });
            setBody("");
            setAttachments([]);
            setMode(null);
            setMentionsInput("");
            setIdempotencyKey(newIdempotencyKey());
        },
    });

    // Insert at the caret rather than replacing the draft, preserving existing text.
    const insertAtCaret = (text: string) => {
        const textarea = textareaRef.current;
        const start = textarea?.selectionStart ?? body.length;
        const end = textarea?.selectionEnd ?? body.length;
        setBody(
            (current) => current.slice(0, start) + text + current.slice(end),
        );
    };

    const { insert: insertQuickReply } = useInsertQuickReply(insertAtCaret);

    const mentionsAllowed =
        mode === "internal" &&
        can(PERMISSIONS.WORKSPACE_TICKET_MESSAGE_MENTION);
    const mentionUuids = mentionsInput
        .split(",")
        .map((value) => value.trim())
        .filter(Boolean);
    const sendError = send.error as { code?: string } | null;
    const mentionError =
        send.fieldErrors.mentions?.join(" ") ||
        (sendError?.code === "mention.not_allowed_on_public_reply"
            ? t("tickets.composer.mention_not_allowed")
            : null);

    // The backend has no concept of a per-reply channel picker: it always
    // requires a `channel` on the message. Internal notes are forced to the
    // "internal" channel server-side regardless of what's sent (see
    // PostTicketMessage::handle). For a public reply we mirror the channel of
    // the most recent inbound customer message so outbound guards (consent,
    // contact-on-file) run against the right channel.
    const lastInboundQuery = useGetTicketMessages(
        ticket.id,
        {
            per_page: 1,
            sort: "-created_at",
            filter: { direction: { eq: "inbound" } },
        } as unknown as GetTicketMessagesParams,
        { query: { enabled: mode === "public" } },
    ) as unknown as { data?: { items: Array<{ channel: MessageChannel }> } };
    const replyChannel: MessageChannel =
        lastInboundQuery.data?.items[0]?.channel ?? "email";

    const customerQuery = useGetCustomer(ticket.customer_id ?? "", {
        query: { enabled: mode === "public" && !!ticket.customer_id },
    }) as unknown as { data?: { name: string } };

    const attachmentsAllClean = attachments.every(
        (attachment) => attachment.scanState === "clean",
    );
    const canSubmit =
        mode !== null &&
        body.trim().length > 0 &&
        attachmentsAllClean &&
        !send.isPending;

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!canSubmit || mode === null) return;

        send.mutate({
            ticket: ticket.id,
            body,
            isInternal: mode === "internal",
            channel: mode === "internal" ? "internal" : replyChannel,
            attachmentUuids: attachments.map((attachment) => attachment.uuid),
            mentions:
                mentionsAllowed && mentionUuids.length > 0
                    ? mentionUuids
                    : undefined,
            idempotencyKey,
        });
    };

    if (isReadOnly) {
        return (
            <section className={styles.root}>
                {t("tickets.composer.read_only")}
            </section>
        );
    }

    return (
        <ActionGuard permission={PERMISSIONS.TICKET_MESSAGE_SEND}>
            <section
                className={`${styles.root} ${mode === "internal" ? styles.internal : ""}`}
                data-workbench-composer
            >
                {send.conflict && (
                    <ConflictBanner
                        error={send.conflict}
                        onReload={send.reloadLatest}
                    />
                )}

                <div
                    className={styles.modes}
                    role="radiogroup"
                    aria-label={t("tickets.composer.mode_label")}
                >
                    <button
                        type="button"
                        role="radio"
                        aria-checked={mode === "public"}
                        onClick={() => setMode("public")}
                        className={`${styles.mode} ${mode === "public" ? styles.modeSelected : ""}`}
                    >
                        {t("tickets.composer.mode_public")}
                    </button>
                    <ActionGuard
                        permission={PERMISSIONS.TICKET_MESSAGE_INTERNAL_WRITE}
                    >
                        <button
                            type="button"
                            role="radio"
                            aria-checked={mode === "internal"}
                            onClick={() => setMode("internal")}
                            className={`${styles.mode} ${mode === "internal" ? styles.modeSelected : ""}`}
                        >
                            {t("tickets.composer.mode_internal")}
                        </button>
                    </ActionGuard>
                </div>

                {mode === "internal" && (
                    <p className={styles.warning}>
                        {t("tickets.composer.internalWarning")}
                    </p>
                )}

                {mode === "public" && ticket.customer_id && (
                    <p className={styles.recipient}>
                        {t("tickets.composer.sending_to", {
                            name: customerQuery.data?.name ?? "…",
                        })}
                    </p>
                )}

                <form onSubmit={handleSubmit}>
                    <div className={styles.utilities}>
                        <button
                            type="button"
                            onClick={() => setPickerOpen((current) => !current)}
                            className={styles.utilityButton}
                        >
                            {t("workspace.quick_replies.insert_button")}
                        </button>
                        {pickerOpen && (
                            <QuickReplyPicker
                                onSelect={(replyId) =>
                                    insertQuickReply(
                                        replyId,
                                        ticket.id,
                                        i18n.language,
                                    )
                                }
                                onClose={() => setPickerOpen(false)}
                            />
                        )}
                    </div>

                    <textarea
                        ref={textareaRef}
                        value={body}
                        onChange={(event) => setBody(event.target.value)}
                        rows={4}
                        placeholder={t("tickets.composer.body_placeholder")}
                        className={styles.textarea}
                    />

                    <AttachmentUploader
                        attachments={attachments}
                        onChange={setAttachments}
                    />

                    {send.fieldErrors.body && (
                        <p className={styles.error}>
                            {send.fieldErrors.body.join(" ")}
                        </p>
                    )}

                    {mentionsAllowed && (
                        <div className={styles.mentions}>
                            <label className={styles.mentionLabel}>
                                {t("tickets.composer.mentions_label")}
                            </label>
                            <input
                                value={mentionsInput}
                                onChange={(event) =>
                                    setMentionsInput(event.target.value)
                                }
                                placeholder={t(
                                    "tickets.composer.mentions_placeholder",
                                )}
                                className={styles.mentionInput}
                            />
                            {mentionError && (
                                <p className={styles.error}>{mentionError}</p>
                            )}
                        </div>
                    )}

                    <div className={styles.footer}>
                        <p className={styles.hint}>
                            {mode === null
                                ? t("tickets.composer.choose_mode_hint")
                                : null}
                        </p>
                        <button
                            type="submit"
                            disabled={!canSubmit}
                            className={styles.submit}
                        >
                            {mode === "internal"
                                ? t("tickets.composer.submit_internal")
                                : t("tickets.composer.submit_public")}
                        </button>
                    </div>
                </form>
            </section>
        </ActionGuard>
    );
}
