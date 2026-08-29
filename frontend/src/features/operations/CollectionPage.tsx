import { useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';
import { apiRequest } from '@/api/http/mutator';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states';
import { TableSkeleton } from '@/shell/states/LoadingState';
import { Badge, Button, Card, Dialog, Input, PageHeader, Pagination, useToast } from '@/components/ui';

export interface CollectionColumn {
  key: string;
  label: string;
  render?: (value: unknown, row: Record<string, unknown>) => ReactNode;
}

interface Props {
  title: string;
  description: string;
  endpoint: string;
  searchEndpoint?: string;
  queryKey: readonly unknown[];
  columns: CollectionColumn[];
  sorts?: Array<{ value: string; label: string }>;
  emptyTitle?: string;
  deleteEndpoint?: (row: Record<string, unknown>) => string;
  actions?: ReactNode;
  rowActions?: (row: Record<string, unknown>) => ReactNode;
}

function display(value: unknown): ReactNode {
  if (value === null || value === undefined || value === '') return <span className="text-slate-400">—</span>;
  if (typeof value === 'boolean') return <Badge tone={value ? 'success' : 'neutral'}>{value ? 'Active' : 'Inactive'}</Badge>;
  if (typeof value === 'object') {
    const record = value as Record<string, unknown>;
    const local = localStorage.getItem('locale') === 'ar' ? record.ar : record.en;
    if (typeof local === 'string') return local;
    return JSON.stringify(value);
  }
  return String(value);
}

export function CollectionPage({ title, description, endpoint, searchEndpoint, queryKey, columns, sorts = [], emptyTitle = 'No records found', deleteEndpoint, actions, rowActions }: Props) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [sort, setSort] = useState('');
  const [pendingDelete, setPendingDelete] = useState<Record<string, unknown> | null>(null);
  const client = useQueryClient();
  const { notify } = useToast();
  const { t } = useTranslation();
  const key = useMemo(() => [...queryKey, page, search, sort], [queryKey, page, search, sort]);
  const query = useQuery({
    queryKey: key,
    queryFn: ({ signal }) => apiRequest<ApiPage<Record<string, unknown>>>({
      url: search && searchEndpoint ? searchEndpoint : endpoint,
      method: 'GET',
      params: { page, per_page: 25, ...(search ? { 'filter[q]': search } : {}), ...(sort ? { sort } : {}) },
      signal,
    }),
  });

  const remove = async () => {
    if (!deleteEndpoint || !pendingDelete) return;
    try {
      await apiRequest({ url: deleteEndpoint(pendingDelete), method: 'DELETE', headers: { 'Idempotency-Key': crypto.randomUUID() } });
      await client.invalidateQueries({ queryKey });
      setPendingDelete(null);
      notify('Record deleted.');
    } catch {
      notify('The record could not be deleted.', 'danger');
    }
  };

  const hasRowActions = Boolean(deleteEndpoint || rowActions);
  return (
    <div className="min-w-0">
      <PageHeader title={title} description={description} actions={actions} />
      <div className="mb-4 grid gap-3 sm:max-w-2xl sm:grid-cols-[minmax(0,1fr)_auto]">
        <Input type="search" aria-label={t('common.search_collection', { title })} placeholder={t('common.search_records')} value={search} onChange={(event) => { setSearch(event.target.value); setPage(1); }} />
        {sorts.length > 0 && <select className="ui-input" aria-label={`Sort ${title}`} value={sort} onChange={(event) => { setSort(event.target.value); setPage(1); }}><option value="">Default order</option>{sorts.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select>}
      </div>
      <AsyncBoundary query={query} loading={<TableSkeleton columns={columns.length + (hasRowActions ? 1 : 0)} />} isEmpty={(data) => data.items.length === 0} empty={<EmptyState title={emptyTitle} action={actions} />}>
        {(data) => <Card className="overflow-hidden"><div className="table-scroll"><table><thead><tr>{columns.map((column) => <th key={column.key} scope="col">{column.label}</th>)}{hasRowActions && <th scope="col">{t('common.actions')}</th>}</tr></thead><tbody>{data.items.map((row, index) => { const rowKey = String(row.uuid ?? row.id ?? row.key ?? `${page}-${index}`); return <tr key={rowKey}>{columns.map((column) => <td key={column.key}>{column.render ? column.render(row[column.key], row) : display(row[column.key])}</td>)}{hasRowActions && <td><div className="flex flex-wrap gap-2">{rowActions?.(row)}{deleteEndpoint && <Button variant="danger" onClick={() => setPendingDelete(row)}>{t('common.delete')}</Button>}</div></td>}</tr>; })}</tbody></table></div><Pagination page={data.meta.page} totalPages={data.meta.total_pages} total={data.meta.total} onPageChange={setPage} /></Card>}
      </AsyncBoundary>
      <Dialog open={Boolean(pendingDelete)} title={t('common.delete_title')} description={t('common.delete_warning')} onClose={() => setPendingDelete(null)} footer={<><Button variant="secondary" onClick={() => setPendingDelete(null)}>{t('common.cancel')}</Button><Button variant="danger" onClick={() => void remove()}>{t('common.delete_permanently')}</Button></>}><p>{t('common.delete_confirm')}</p></Dialog>
    </div>
  );
}
