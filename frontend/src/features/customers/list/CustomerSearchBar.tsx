import { useTranslation } from 'react-i18next';
import type { CustomerStatus } from '../types';

const STATUS_OPTIONS: CustomerStatus[] = ['active', 'blocked', 'anonymised'];

interface CustomerSearchBarProps {
  search: string;
  status?: CustomerStatus;
  onSearchChange: (value: string) => void;
  onStatusChange: (status: CustomerStatus | undefined) => void;
  onClear: () => void;
}

export function CustomerSearchBar({
  search,
  status,
  onSearchChange,
  onStatusChange,
  onClear,
}: CustomerSearchBarProps) {
  const { t } = useTranslation();
  const hasActiveFilters = Boolean(search) || Boolean(status);

  return (
    <div className="flex flex-wrap items-end gap-3 rounded border border-gray-200 bg-gray-50 p-3">
      <div className="flex flex-col">
        <label htmlFor="customer-search" className="text-xs font-medium text-gray-600">
          {t('customers.filters.search_label')}
        </label>
        <input
          id="customer-search"
          type="search"
          value={search}
          onChange={(event) => onSearchChange(event.target.value)}
          placeholder={t('customers.filters.search_placeholder')}
          className="w-72 rounded border border-gray-300 px-2 py-1 text-sm"
        />
      </div>

      <div className="flex flex-col">
        <label htmlFor="customer-filter-status" className="text-xs font-medium text-gray-600">
          {t('customers.filters.status_label')}
        </label>
        <select
          id="customer-filter-status"
          value={status ?? ''}
          onChange={(event) => onStatusChange((event.target.value || undefined) as CustomerStatus | undefined)}
          className="rounded border border-gray-300 px-2 py-1 text-sm"
        >
          <option value="">{t('customers.filters.any')}</option>
          {STATUS_OPTIONS.map((option) => (
            <option key={option} value={option}>
              {t(`customers.status.${option}`)}
            </option>
          ))}
        </select>
      </div>

      {hasActiveFilters && (
        <button
          type="button"
          onClick={onClear}
          className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
        >
          {t('customers.filters.clear')}
        </button>
      )}
    </div>
  );
}
