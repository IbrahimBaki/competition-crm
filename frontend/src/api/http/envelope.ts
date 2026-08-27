import { z } from 'zod';

export class MalformedEnvelopeError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'MalformedEnvelopeError';
  }
}

const apiPageMetaSchema = z.object({
  request_id: z.string(),
  page: z.number(),
  per_page: z.number(),
  total: z.number(),
  total_pages: z.number(),
  sort: z.string().optional(),
  filters: z.record(z.unknown()).optional(),
});

export type ApiPageMeta = z.infer<typeof apiPageMetaSchema>;

const apiSuccessSchema = z.object({
  data: z.unknown(),
  meta: z.record(z.unknown()).optional(),
  links: z.record(z.string().or(z.null())).optional(),
});

export type ApiSuccess<T> = {
  data: T;
  meta?: Record<string, unknown>;
  links?: Record<string, string | null>;
};

export type ApiPage<T> = {
  items: T[];
  meta: ApiPageMeta;
};

export function unwrap<T>(body: unknown): T {
  try {
    const parsed = apiSuccessSchema.parse(body);
    return parsed.data as T;
  } catch (error) {
    throw new MalformedEnvelopeError(
      `Failed to unwrap API response: ${error instanceof Error ? error.message : String(error)}`
    );
  }
}

export function unwrapPage<T>(body: unknown): ApiPage<T> {
  try {
    const parsed = apiSuccessSchema.parse(body);
    const meta = apiPageMetaSchema.parse(parsed.meta);
    return {
      items: Array.isArray(parsed.data) ? (parsed.data as T[]) : [],
      meta,
    };
  } catch (error) {
    throw new MalformedEnvelopeError(
      `Failed to unwrap paginated API response: ${error instanceof Error ? error.message : String(error)}`
    );
  }
}
