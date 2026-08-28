import { useTranslation } from 'react-i18next';

export function ChannelsPage() {
  const { t } = useTranslation();

  return (
    <div>
      <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.channels.title')}</h1>
      <div className="rounded border border-yellow-200 bg-yellow-50 p-4">
        <p className="text-sm text-yellow-800">
          {t('admin.channels.blocked_by_backend_note')}
          <br />
          <span className="text-xs text-yellow-700">
            TODO(FE-06): Channel health and email replay are blocked by missing generated client operations
            for inbound email state transitions. See .squad/gaps/36-483.md.
          </span>
        </p>
      </div>
    </div>
  );
}
