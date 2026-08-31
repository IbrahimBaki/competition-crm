import { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';
import { apiRequest } from '@/api/http/mutator';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { TableSkeleton } from '@/shell/states/LoadingState';
import { Button, Card } from '@/components/ui';
import { EmptyState } from '@/shell/states';
import { ResourceField, ResourceFormDialog } from '@/features/operations/ResourceFormDialog';

type Row = Record<string, unknown>;

function flattenCategories(rows: Row[]): Row[] {
  return rows.flatMap((row) => [row, ...flattenCategories(Array.isArray(row.children) ? row.children as Row[] : [])]);
}

function categoryName(row: Row): Record<string, unknown> | undefined {
  return row.name as Record<string, unknown> | undefined;
}

export function KnowledgeCategoriesPanel() {
  const { t } = useTranslation();
  const [editing, setEditing] = useState<Row | null | undefined>(undefined);
  const query = useQuery({ queryKey: ['knowledge', 'categories'], queryFn: ({ signal }) => apiRequest<Row[]>({ url: '/knowledge/categories', method: 'GET', signal }) });
  const rows = useMemo(() => flattenCategories(query.data ?? []), [query.data]);
  const fields = useMemo<ResourceField[]>(() => [
    { key: 'code', label: 'Code', required: true },
    { key: 'name_en', label: 'English name', required: true },
    { key: 'name_ar', label: 'Arabic name', required: true },
    {
      key: 'parent_id', label: 'Parent category', kind: 'select', options: rows
        .filter((row) => String(row.id) !== String(editing?.id))
        .map((row) => ({ value: String(row.id), label: `${'— '.repeat(Math.max(0, Number(row.depth) - 1))}${String(categoryName(row)?.en ?? categoryName(row)?.ar ?? row.code)}` })),
    },
    { key: 'position', label: 'Position', kind: 'number' },
    { key: 'is_active', label: 'Active', kind: 'checkbox' },
  ], [editing?.id, rows]);

  return (
    <Card className="p-5">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 className="text-lg font-semibold">{t('pages.knowledge.categories')}</h2><p className="mt-1 text-sm text-slate-500">{t('pages.knowledge.categories_description')}</p></div>
        <Button onClick={() => setEditing(null)}>{t('pages.knowledge.new_category')}</Button>
      </div>
      <AsyncBoundary query={query} loading={<div className="mt-4"><TableSkeleton columns={4} rows={5}/></div>}>
        {() => rows.length === 0 ? <div className="mt-4"><EmptyState title={t('pages.knowledge.empty_categories')} description={t('pages.knowledge.empty_categories_description')} action={<Button onClick={() => setEditing(null)}>{t('pages.knowledge.new_category')}</Button>}/></div> : <div className="table-scroll mt-4"><table className="ui-data-table"><thead><tr><th>{t('pages.knowledge.category')}</th><th>{t('pages.knowledge.code')}</th><th>{t('pages.knowledge.position')}</th><th>{t('common.actions')}</th></tr></thead><tbody>{rows.map((row) => <tr key={String(row.id)}><td data-label={t('pages.knowledge.category')} style={{ paddingInlineStart: `${Math.max(0, Number(row.depth) - 1) * 1.25 + 0.75}rem` }}>{String(categoryName(row)?.en ?? categoryName(row)?.ar ?? '')}</td><td data-label={t('pages.knowledge.code')}>{String(row.code ?? '')}</td><td data-label={t('pages.knowledge.position')}>{String(row.position ?? 0)}</td><td data-label={t('common.actions')}><Button variant="secondary" onClick={() => setEditing(row)}>Edit</Button></td></tr>)}</tbody></table></div>}
      </AsyncBoundary>
      <ResourceFormDialog
        open={editing !== undefined}
        title={editing ? t('pages.knowledge.edit_category') : t('pages.knowledge.new_category_dialog')}
        endpoint={editing ? `/knowledge/categories/${String(editing.id)}` : '/knowledge/categories'}
        method={editing ? 'PATCH' : 'POST'}
        queryKey={['knowledge', 'categories']}
        fields={fields}
        initial={editing ? { ...editing, name_en: categoryName(editing)?.en, name_ar: categoryName(editing)?.ar } : { parent_id: '', position: 0, is_active: true }}
        transform={(values) => { const { name_en, name_ar, ...rest } = values; return { ...rest, parent_id: rest.parent_id || null, name: { en: name_en, ar: name_ar } }; }}
        onClose={() => setEditing(undefined)}
      />
    </Card>
  );
}
