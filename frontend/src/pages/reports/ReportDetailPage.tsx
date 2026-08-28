import { useTranslation } from 'react-i18next';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { fetchReport, fetchReportDefinitions } from '@/features/reports/api/wire';
import { useReportFilters } from '@/features/reports/filters/useReportFilters';
import { ReportFilterBar } from '@/features/reports/filters/ReportFilterBar';
import { ExportMenu } from '@/features/reports/export/ExportMenu';
import { LoadingState, ErrorState, NotFoundState } from '@/shell/states';

export function ReportDetailPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { reportId } = useParams<{ reportId: string }>();

  if (!reportId) {
    return <NotFoundState />;
  }

  const { values: filters, isComplete } = useReportFilters();

  const { data: definitions } = useQuery({
    queryKey: ['reports'],
    queryFn: fetchReportDefinitions,
  });

  const definition = definitions?.find((d) => d.key === reportId);

  const { data: result, isLoading, error } = useQuery({
    queryKey: ['report', reportId, filters],
    queryFn: () => fetchReport(reportId, filters),
    enabled: isComplete(),
  });

  if (!definition) {
    return <NotFoundState />;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="bg-white border-b p-4">
        <div className="max-w-6xl mx-auto">
          <h1 className="text-3xl font-bold text-gray-900">
            {t(`reports.column.${reportId}.title`, { defaultValue: reportId })}
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
            {/* Export Menu */}
            <div className="bg-white rounded-lg shadow p-4">
              <h3 className="text-sm font-medium text-gray-900 mb-3">{t('reports.export.title')}</h3>
              <ExportMenu reportId={reportId} filters={filters} />
            </div>

            {/* Results Table */}
            <div className="bg-white rounded-lg shadow">
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      {definition.columns.map((colKey) => (
                        <th
                          key={colKey}
                          className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                          {t(`reports.column.${reportId}.${colKey}`, { defaultValue: colKey })}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {result.rows.length === 0 ? (
                      <tr>
                        <td
                          colSpan={definition.columns.length}
                          className="px-6 py-4 text-sm text-gray-500 text-center"
                        >
                          {t('reports.table.no_data')}
                        </td>
                      </tr>
                    ) : (
                      result.rows.map((row, idx) => (
                        <tr key={idx}>
                          {definition.columns.map((colKey) => {
                            // Drill-down link for ticket-related columns
                            const cellValue = String(row[colKey] ?? '');
                            const isTicketColumn = ['ticket_id', 'reference'].includes(colKey);

                            return (
                              <td
                                key={colKey}
                                className="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                              >
                                {isTicketColumn ? (
                                  <button
                                    onClick={() => navigate(`/tickets/${cellValue}`)}
                                    className="text-blue-600 hover:text-blue-900 hover:underline"
                                  >
                                    {cellValue}
                                  </button>
                                ) : (
                                  cellValue
                                )}
                              </td>
                            );
                          })}
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
