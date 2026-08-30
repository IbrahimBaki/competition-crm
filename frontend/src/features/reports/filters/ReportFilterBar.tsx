import { type ChangeEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useReportFilters } from './useReportFilters';

/**
 * Shared filter bar used by all reports.
 * Filters are driven by the global report filter descriptors (date_from, date_to, etc.).
 * All filter state lives in the URL query string for shareability and back/forward.
 */
export function ReportFilterBar() {
  const { t } = useTranslation();
  const { values, setFilter, reset, isComplete } = useReportFilters();

  const handleDateFromChange = (e: ChangeEvent<HTMLInputElement>) => {
    setFilter('date_from', e.target.value || undefined);
  };

  const handleDateToChange = (e: ChangeEvent<HTMLInputElement>) => {
    setFilter('date_to', e.target.value || undefined);
  };

  const handleReset = () => {
    reset();
  };

  const showValidationError = !isComplete();

  return (
    <div className="bg-white border-b p-4">
      <div className="max-w-6xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Date From */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {t('reports.filter.date_from_label')} *
            </label>
            <input
              type="date"
              value={values.date_from || ''}
              onChange={handleDateFromChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          {/* Date To */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {t('reports.filter.date_to_label')} *
            </label>
            <input
              type="date"
              value={values.date_to || ''}
              onChange={handleDateToChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          {/* Timezone */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {t('reports.filter.timezone_label')}
            </label>
            <input
              type="text"
              placeholder={t('reports.filter.timezone_placeholder')}
              value={values.timezone || ''}
              onChange={(e) => setFilter('timezone', e.target.value || undefined)}
              disabled
              className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
              title="Timezone is set to your browser's timezone"
            />
          </div>
        </div>

        {/* Validation Error */}
        {showValidationError && (
          <div className="mt-3 text-sm text-red-600">
            {t('reports.filter.required_error')}
          </div>
        )}

        {/* Reset Button */}
        <div className="mt-4 flex justify-end">
          <button
            onClick={handleReset}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {t('reports.filter.reset_button')}
          </button>
        </div>
      </div>
    </div>
  );
}
