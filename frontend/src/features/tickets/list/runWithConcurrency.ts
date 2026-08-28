export interface ConcurrencyResult<T> {
  item: T;
  ok: boolean;
  error?: string;
}

/**
 * Runs `worker` over `items` with at most `limit` in flight at once, calling
 * `onProgress` after each completion. Used for bulk ticket actions, since no
 * bulk endpoint exists on the backend (see TicketBulkActions.tsx).
 */
export async function runWithConcurrency<T>(
  items: T[],
  worker: (item: T) => Promise<void>,
  limit: number,
  onProgress?: (completed: number, total: number) => void
): Promise<ConcurrencyResult<T>[]> {
  const results: ConcurrencyResult<T>[] = [];
  let cursor = 0;
  let completed = 0;

  async function runNext(): Promise<void> {
    const index = cursor++;
    if (index >= items.length) return;
    const item = items[index] as T;

    try {
      await worker(item);
      results[index] = { item, ok: true };
    } catch (error) {
      // apiRequest rejects with a NormalisedApiError (plain object with a
      // `message` field), not necessarily an Error instance.
      const message =
        error && typeof error === 'object' && 'message' in error
          ? String((error as { message: unknown }).message)
          : String(error);
      results[index] = { item, ok: false, error: message };
    } finally {
      completed += 1;
      onProgress?.(completed, items.length);
      await runNext();
    }
  }

  const workers = Array.from({ length: Math.min(limit, items.length) }, () => runNext());
  await Promise.all(workers);
  return results;
}
