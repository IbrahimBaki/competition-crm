export type BilingualValue = { ar?: string | null; en?: string | null } | string | null | undefined;

export function pickBilingual(value: BilingualValue, locale: string): string {
  if (!value) return '';
  if (typeof value === 'string') return value;

  const preferred = locale === 'ar' ? value.ar : value.en;
  return preferred || value.en || value.ar || '';
}
