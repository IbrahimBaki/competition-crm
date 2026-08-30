import { useRenderQuickReply } from '../api/wire';

/**
 * Never resolve placeholders client-side — the rendered body always comes
 * from POST quick-replies/render (app/Domains/Workspace/Services/QuickReplyRenderer.php).
 * Locale selection picks which of the response's {en, ar} strings to insert.
 */
export function useInsertQuickReply(onInsert: (body: string) => void) {
  const mutation = useRenderQuickReply({
    onSuccess: (rendered, variables) => {
      const locale = (variables as { locale?: string }).locale === 'ar' ? 'ar' : 'en';
      onInsert(locale === 'ar' ? rendered.ar : rendered.en);
    },
  });

  return {
    insert: (replyId: string, ticketId: string | null, locale: string) =>
      mutation.mutate({ replyId, ticketId, locale } as never),
    isPending: mutation.isPending,
  };
}
