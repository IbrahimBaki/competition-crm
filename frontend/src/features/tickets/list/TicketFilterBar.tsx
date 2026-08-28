import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useGetTicketCategories } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import { pickBilingual } from '../utils/bilingual';
import { statusLabelKey, priorityLabelKey } from '../utils/labels';
import type { TicketCategory, TicketLifecycleType, TicketPriority } from '../types';
import type { TicketFilterKey, TicketFilters } from './useTicketListQuery';

const STATUS_OPTIONS: TicketLifecycleType[] = ['new', 'open', 'pending', 'resolved', 'closed', 'spam'];
const PRIORITY_OPTIONS: TicketPriority[] = ['low', 'normal', 'high', 'urgent'];

const SEARCH_DEBOUNCE_MS = 300;

interface TicketFilterBarProps {
  allowedFilterKeys: readonly TicketFilterKey[];
  filters: TicketFilters;
  search?: string;
  departmentNames: Map<string, string>;
  onFilterChange: (key: TicketFilterKey, value: string | undefined) => void;
  onSearchChange: (value: string | undefined) => void;
  onClear: () => void;
}

export function TicketFilterBar({
  allowedFilterKeys,
  filters,
  search,
  departmentNames,
  onFilterChange,
  onSearchChange,
  onClear,
}: TicketFilterBarProps) {
  const { t, i18n } = useTranslation();
  const [searchDraft, setSearchDraft] = useState(search ?? '');

  useEffect(() => {
    setSearchDraft(search ?? '');
  }, [search]);

  useEffect(() => {
    const handle = setTimeout(() => {
      if (searchDraft !== (search ?? '')) {
        onSearchChange(searchDraft || undefined);
      }
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, SEARCH_DEBOUNCE_MS);
    return () => clearTimeout(handle);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchDraft]);

  const categoriesQuery = useGetTicketCategories(
    { per_page: 100 },
    { query: { enabled: allowedFilterKeys.includes('category') } }
  ) as unknown as { data?: ApiPage<TicketCategory> };

  const hasActiveFilters = Boolean(search) || Object.values(filters).some(Boolean);

  return (
    <div className="flex flex-wrap items-end gap-3 rounded border border-gray-200 bg-gray-50 p-3">
      <div className="flex flex-col">
        <label htmlFor="ticket-search" className="text-xs font-medium text-gray-600">
          {t('tickets.filters.search_label')}
        </label>
        <input
          id="ticket-search"
          type="search"
          value={searchDraft}
          onChange={(event) => setSearchDraft(event.target.value)}
          placeholder={t('tickets.filters.search_placeholder')}
          className="rounded border border-gray-300 px-2 py-1 text-sm"
        />
      </div>

      {allowedFilterKeys.includes('status') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-status" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.status_label')}
          </label>
          <select
            id="ticket-filter-status"
            value={filters.status ?? ''}
            onChange={(event) => onFilterChange('status', event.target.value || undefined)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
          >
            <option value="">{t('tickets.filters.any')}</option>
            {STATUS_OPTIONS.map((status) => (
              <option key={status} value={status}>
                {t(statusLabelKey(status))}
              </option>
            ))}
          </select>
        </div>
      )}

      {allowedFilterKeys.includes('priority') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-priority" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.priority_label')}
          </label>
          <select
            id="ticket-filter-priority"
            value={filters.priority ?? ''}
            onChange={(event) => onFilterChange('priority', event.target.value || undefined)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
          >
            <option value="">{t('tickets.filters.any')}</option>
            {PRIORITY_OPTIONS.map((priority) => (
              <option key={priority} value={priority}>
                {t(priorityLabelKey(priority))}
              </option>
            ))}
          </select>
        </div>
      )}

      {allowedFilterKeys.includes('department') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-department" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.department_label')}
          </label>
          <select
            id="ticket-filter-department"
            value={filters.department ?? ''}
            onChange={(event) => onFilterChange('department', event.target.value || undefined)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
          >
            <option value="">{t('tickets.filters.any')}</option>
            {[...departmentNames.entries()].map(([id, name]) => (
              <option key={id} value={id}>
                {name}
              </option>
            ))}
          </select>
        </div>
      )}

      {allowedFilterKeys.includes('category') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-category" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.category_label')}
          </label>
          <select
            id="ticket-filter-category"
            value={filters.category ?? ''}
            onChange={(event) => onFilterChange('category', event.target.value || undefined)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
          >
            <option value="">{t('tickets.filters.any')}</option>
            {(categoriesQuery.data?.items ?? []).map((category) => (
              <option key={category.id} value={category.id}>
                {pickBilingual(category.name, i18n.language)}
              </option>
            ))}
          </select>
        </div>
      )}

      {allowedFilterKeys.includes('assignee') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-assignee" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.assignee_label')}
          </label>
          <input
            id="ticket-filter-assignee"
            type="text"
            value={filters.assignee ?? ''}
            onChange={(event) => onFilterChange('assignee', event.target.value || undefined)}
            placeholder={t('tickets.filters.uuid_placeholder')}
            className="w-40 rounded border border-gray-300 px-2 py-1 text-sm"
          />
        </div>
      )}

      {allowedFilterKeys.includes('customer') && (
        <div className="flex flex-col">
          <label htmlFor="ticket-filter-customer" className="text-xs font-medium text-gray-600">
            {t('tickets.filters.customer_label')}
          </label>
          <input
            id="ticket-filter-customer"
            type="text"
            value={filters.customer ?? ''}
            onChange={(event) => onFilterChange('customer', event.target.value || undefined)}
            placeholder={t('tickets.filters.uuid_placeholder')}
            className="w-40 rounded border border-gray-300 px-2 py-1 text-sm"
          />
        </div>
      )}

      {hasActiveFilters && (
        <button
          type="button"
          onClick={onClear}
          className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
        >
          {t('tickets.filters.clear')}
        </button>
      )}
    </div>
  );
}
