import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { fetchReport, fetchReportDefinitions } from '@/features/reports/api/wire';
import { useReportFilters } from '@/features/reports/filters/useReportFilters';
import { ReportFilterBar } from '@/features/reports/filters/ReportFilterBar';
import { LoadingState, ErrorState, EmptyState } from '@/shell/states';

// Dashboard report key from ManagementDashboardReport.php:22
const DASHBOARD_REPORT_KEY = 'management_dashboard';

export function ManagementDashboardPage() {
  const { t } = useTranslation();
  const { values: filters, isComplete } = useReportFilters();

  const { data: definitions } = useQuery({
    queryKey: ['reports'],
    queryFn: fetchReportDefinitions,
  });

  const definition = definitions?.find((d) => d.key === DASHBOARD_REPORT_KEY);

  const { data: result, isLoading, error } = useQuery({
    queryKey: ['report', DASHBOARD_REPORT_KEY, filters],
    queryFn: () => fetchReport(DASHBOARD_REPORT_KEY, filters),
    enabled: isComplete(),
  });

  if (!definition) {
    return (
      <ErrorState
        error={{
          status: 404,
          code: 'not_found',
          message: 'Dashboard report not found',
          fieldErrors: {},
          requestId: null,
          kind: 'unknown',
        } as any}
      />
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="bg-white border-b p-4">
        <div className="max-w-6xl mx-auto">
          <h1 className="text-3xl font-bold text-gray-900">
            {t('reports.column.management_dashboard.title')}
          </h1>
        </div>
      </div>

      <ReportFilterBar />

      <div className="max-w-6xl mx-auto px-4 py-8">
        {!isComplete() && (
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
            {t('reports.filter.required_error')}
          </div>
        )}

        {isComplete() && isLoading && <LoadingState />}

        {isComplete() && error && <ErrorState error={error as any} />}

        {isComplete() && result && (
          <div className="space-y-6">
            {/* Summary Metrics */}
            {result.totals && (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {Object.entries(result.totals).map(([key, value]) => (
                  <div key={key} className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-sm font-medium text-gray-600 mb-2">
                      {t(`reports.column.management_dashboard.${key}`, { defaultValue: key })}
                    </h3>
                    <p className="text-2xl font-bold text-gray-900">
                      {typeof value === 'object'
                        ? JSON.stringify(value)
                        : String(value)}
                    </p>
                  </div>
                ))}
              </div>
            )}

            {/* Data Table */}
            {result.rows && result.rows.length > 0 && (
              <div className="bg-white rounded-lg shadow overflow-hidden">
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        {definition.columns.map((colKey) => (
                          <th
                            key={colKey}
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                          >
                            {t(`reports.column.management_dashboard.${colKey}`, { defaultValue: colKey })}
                          </th>
                        ))}
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {result.rows.map((row, idx) => (
                        <tr key={idx}>
                          {definition.columns.map((colKey) => (
                            <td key={colKey} className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {String(row[colKey] ?? '')}
                            </td>
                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {result.rows.length === 0 && (
              <EmptyState
                title={t('reports.dashboard.no_data')}
                description="No data available for your selected filters and scope."
              />
            )}
          </div>
        )}
      </div>
    </div>
  );
}
