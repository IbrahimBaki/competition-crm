import { useTranslation } from 'react-i18next';
import type { NormalisedApiError } from '@/api/http/errors';

interface ConflictBannerProps {
  error: NormalisedApiError;
  onReload: () => void;
}

export function ConflictBanner({ error, onReload }: ConflictBannerProps) {
  const { t } = useTranslation();

  return (
    <div
      role="alert"
      className="flex items-center justify-between gap-4 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900"
    >
      <p className="text-sm">{error.message}</p>
      <button
        type="button"
        onClick={onReload}
        className="shrink-0 rounded bg-amber-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-700"
      >
        {t('tickets.conflict.reload_latest')}
      </button>
    </div>
  );
}
