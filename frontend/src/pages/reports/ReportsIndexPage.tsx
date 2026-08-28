import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { fetchReportDefinitions } from '@/features/reports/api/wire';
import { LoadingState, EmptyState, ErrorState } from '@/shell/states';

export function ReportsIndexPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const { data: definitions, isLoading, error } = useQuery({
    queryKey: ['reports'],
    queryFn: fetchReportDefinitions,
  });

  if (isLoading) {
    return <LoadingState />;
  }

  if (error) {
    return <ErrorState error={error as any} />;
  }

  if (!definitions || definitions.length === 0) {
    return (
      <EmptyState
        title={t('reports.empty_title')}
        description="No reports are available to you."
      />
    );
  }

  return (
    <div className="max-w-6xl mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-6">{t('reports.title')}</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {definitions.map((def) => (
          <button
            key={def.key}
            onClick={() => navigate(`/reports/${def.key}`)}
            className="p-6 bg-white border border-gray-200 rounded-lg hover:shadow-lg hover:border-blue-300 transition-all text-left"
          >
            <h3 className="text-lg font-semibold text-gray-900 mb-2">
              {t(`reports.column.${def.key}.title`, { defaultValue: def.key })}
            </h3>
            <p className="text-sm text-gray-600">
              Click to view details and filter data
            </p>
          </button>
        ))}
      </div>
    </div>
  );
}
