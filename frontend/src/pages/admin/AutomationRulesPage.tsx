import { useTranslation } from 'react-i18next';

export function AutomationRulesPage() {
  const { t } = useTranslation();

  return (
    <div>
      <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.automation.title')}</h1>
      <div className="rounded border border-yellow-200 bg-yellow-50 p-4">
        <p className="text-sm text-yellow-800">
          {t('admin.automation.blocked_by_backend_note')}
          <br />
          <span className="text-xs text-yellow-700">
            TODO(FE-06): Automation rule CRUD is blocked by missing generated client operations.
            Routes exist in routes/api.php but were not captured in the frozen OpenAPI spec.
            See .squad/gaps/36-483.md.
          </span>
        </p>
      </div>
    </div>
  );
}
