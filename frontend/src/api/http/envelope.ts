import { z } from 'zod';

export class MalformedEnvelopeError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'MalformedEnvelopeError';
  }
}

const apiPageMetaSchema = z.object({
  request_id: z.string().default(''),
  page: z.number(),
  per_page: z.number(),
  total: z.number(),
  total_pages: z.number().optional(),
  sort: z.string().nullable().optional(),
  filters: z.record(z.unknown()).nullable().optional(),
});

export type ApiPageMeta = Omit<z.infer<typeof apiPageMetaSchema>, 'total_pages'> & { total_pages: number };

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
    const normalisedMeta: ApiPageMeta = { ...meta, total_pages: meta.total_pages ?? Math.max(1, Math.ceil(meta.total / Math.max(meta.per_page, 1))) };
    return {
      items: Array.isArray(parsed.data) ? (parsed.data as T[]) : [],
      meta: normalisedMeta,
    };
  } catch (error) {
    throw new MalformedEnvelopeError(
      `Failed to unwrap paginated API response: ${error instanceof Error ? error.message : String(error)}`
    );
  }
}
