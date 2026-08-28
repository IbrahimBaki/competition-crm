import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ForbiddenState } from '@/shell/states/ForbiddenState';
import { NotFoundState } from '@/shell/states/NotFoundState';
import { ActionGuard } from '@/shell/ActionGuard';
import { RequirePermission } from '@/auth/RequirePermission';
import { PERMISSIONS } from '@/auth/permissions';
import { useGetDepartments } from '@/api/generated/organization/organization';
import type { ApiPage } from '@/api/http/envelope';
import { useTicketListQuery, type TicketQueueMode } from '@/features/tickets/list/useTicketListQuery';
import { useDepartmentNames } from '@/features/tickets/utils/useDepartmentNames';
import { TicketListTable } from '@/features/tickets/list/TicketListTable';
import { TicketFilterBar } from '@/features/tickets/list/TicketFilterBar';
import { SavedViewsBar } from '@/features/tickets/list/SavedViewsBar';
import { TicketBulkActions } from '@/features/tickets/list/TicketBulkActions';

interface DepartmentOption {
  id: string;
  name: string;
}

export function TicketsPage() {
  const { t } = useTranslation();
  const [mode, setMode] = useState<TicketQueueMode>('all');
  const [departmentId, setDepartmentId] = useState<string | undefined>(undefined);
  const [selected, setSelected] = useState<Set<string>>(new Set());

  const { state, setState, setFilter, clearFilters, applyState, allowedFilterKeys, query } = useTicketListQuery(
    mode,
    departmentId
  );
  const { byId: departmentNames } = useDepartmentNames();

  const departmentsQuery = useGetDepartments(
    { per_page: 100 },
    { query: { enabled: mode === 'department' } }
  ) as unknown as { data?: ApiPage<DepartmentOption> };

  const toggleRow = (uuid: string) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(uuid)) next.delete(uuid);
      else next.add(uuid);
      return next;
    });
  };

  const toggleAll = () => {
    const rows = query.data?.items ?? [];
    setSelected((prev) => {
      const allSelected = rows.length > 0 && rows.every((row) => prev.has(row.uuid));
      return allSelected ? new Set() : new Set(rows.map((row) => row.uuid));
    });
  };

  const handleModeChange = (nextMode: TicketQueueMode) => {
    setMode(nextMode);
    setSelected(new Set());
    if (nextMode !== 'department') setDepartmentId(undefined);
  };

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">{t('pages.tickets.title')}</h1>

        <div className="flex gap-2">
          <button
            type="button"
            onClick={() => handleModeChange('all')}
            className={`rounded px-3 py-1.5 text-sm ${mode === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'}`}
          >
            {t('tickets.mode.all')}
          </button>
          <ActionGuard permission={PERMISSIONS.TICKETS_QUEUE_VIEW}>
            <button
              type="button"
              onClick={() => handleModeChange('mine')}
              className={`rounded px-3 py-1.5 text-sm ${mode === 'mine' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'}`}
            >
              {t('tickets.mode.mine')}
            </button>
            <button
              type="button"
              onClick={() => handleModeChange('department')}
              className={`rounded px-3 py-1.5 text-sm ${mode === 'department' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'}`}
            >
              {t('tickets.mode.department')}
            </button>
          </ActionGuard>
        </div>
      </div>

      {mode === 'department' && (
        <div className="mb-4">
          <label htmlFor="queue-department" className="me-2 text-sm font-medium text-gray-600">
            {t('tickets.mode.department_select_label')}
          </label>
          <select
            id="queue-department"
            value={departmentId ?? ''}
            onChange={(event) => setDepartmentId(event.target.value || undefined)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
          >
            <option value="">{t('tickets.mode.department_select_placeholder')}</option>
            {(departmentsQuery.data?.items ?? []).map((department) => (
              <option key={department.id} value={department.id}>
                {department.name}
              </option>
            ))}
          </select>
        </div>
      )}

      <RequirePermission permission={PERMISSIONS.TICKETS_VIEW_ANY} fallback={<ForbiddenState />}>
        <div className="mb-4 flex flex-col gap-3">
          <SavedViewsBar
            currentSort={state.sort}
            currentSearch={state.search}
            currentFilters={state.filters}
            onApply={(view) =>
              applyState({ sort: view.sort, search: view.search, filters: view.filters, perPage: state.perPage })
            }
          />
          <TicketFilterBar
            allowedFilterKeys={allowedFilterKeys}
            filters={state.filters}
            search={state.search}
            departmentNames={departmentNames}
            onFilterChange={setFilter}
            onSearchChange={(value) => setState({ search: value })}
            onClear={clearFilters}
          />
        </div>

        <TicketBulkActions selected={[...selected]} onCleared={() => setSelected(new Set())} />

        <div className="mt-4 rounded border border-gray-200 bg-white">
          <AsyncBoundary
            query={query}
            isEmpty={(page) => page.items.length === 0}
            empty={
              <EmptyState
                title={t('tickets.list.empty_title')}
                description={t('tickets.list.empty_description')}
                action={
                  <button type="button" onClick={clearFilters} className="text-blue-600 hover:underline">
                    {t('tickets.filters.clear')}
                  </button>
                }
              />
            }
            error={
              query.error && (query.error as { status?: number }).status === 403 ? (
                <ForbiddenState />
              ) : query.error && (query.error as { status?: number }).status === 404 ? (
                <NotFoundState />
              ) : undefined
            }
          >
            {(page) => (
              <>
                <TicketListTable
                  rows={page.items}
                  sort={state.sort}
                  onSortChange={(sort) => setState({ sort })}
                  selected={selected}
                  onToggleRow={toggleRow}
                  onToggleAll={toggleAll}
                  departmentNames={departmentNames}
                />
                <div className="flex items-center justify-between border-t border-gray-100 px-3 py-2 text-sm text-gray-600">
                  <span>
                    {t('tickets.list.pagination_summary', {
                      page: page.meta.page,
                      totalPages: page.meta.total_pages,
                      total: page.meta.total,
                    })}
                  </span>
                  <div className="flex gap-2">
                    <button
                      type="button"
                      disabled={page.meta.page <= 1}
                      onClick={() => setState({ page: page.meta.page - 1 })}
                      className="rounded border border-gray-300 px-2 py-1 disabled:opacity-40"
                    >
                      {t('tickets.list.previous_page')}
                    </button>
                    <button
                      type="button"
                      disabled={page.meta.page >= page.meta.total_pages}
                      onClick={() => setState({ page: page.meta.page + 1 })}
                      className="rounded border border-gray-300 px-2 py-1 disabled:opacity-40"
                    >
                      {t('tickets.list.next_page')}
                    </button>
                  </div>
                </div>
              </>
            )}
          </AsyncBoundary>
        </div>
      </RequirePermission>
    </div>
  );
}
