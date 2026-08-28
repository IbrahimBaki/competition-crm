import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { TicketListRow } from '../types';
import type { TicketSortField } from './useTicketListQuery';
import { statusBadgeClass, statusLabelKey, priorityBadgeClass, priorityLabelKey } from '../utils/labels';

interface SortableColumn {
  field: TicketSortField;
  labelKey: string;
}

const COLUMNS: SortableColumn[] = [
  { field: 'reference', labelKey: 'tickets.list.column.reference' },
  { field: 'status', labelKey: 'tickets.list.column.status' },
  { field: 'priority', labelKey: 'tickets.list.column.priority' },
  { field: 'updated_at', labelKey: 'tickets.list.column.updated_at' },
];

interface TicketListTableProps {
  rows: TicketListRow[];
  sort?: string;
  onSortChange: (sort: string) => void;
  selected: Set<string>;
  onToggleRow: (uuid: string) => void;
  onToggleAll: () => void;
  departmentNames: Map<string, string>;
}

export function TicketListTable({
  rows,
  sort,
  onSortChange,
  selected,
  onToggleRow,
  onToggleAll,
  departmentNames,
}: TicketListTableProps) {
  const { t, i18n } = useTranslation();
  const dateFormatter = new Intl.DateTimeFormat(i18n.language, {
    dateStyle: 'medium',
    timeStyle: 'short',
  });

  const sortField = sort?.replace(/^-/, '');
  const sortDescending = sort?.startsWith('-') ?? false;

  const handleHeaderClick = (field: TicketSortField) => {
    if (sortField === field) {
      onSortChange(sortDescending ? field : `-${field}`);
    } else {
      onSortChange(`-${field}`);
    }
  };

  const allSelected = rows.length > 0 && rows.every((row) => selected.has(row.uuid));

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th className="w-10 px-3 py-2">
              <input
                type="checkbox"
                aria-label={t('tickets.list.select_all')}
                checked={allSelected}
                onChange={onToggleAll}
              />
            </th>
            {COLUMNS.map((column) => (
              <th
                key={column.field}
                scope="col"
                className="px-3 py-2 text-start font-medium text-gray-600 cursor-pointer select-none"
                onClick={() => handleHeaderClick(column.field)}
              >
                {t(column.labelKey)}
                {sortField === column.field && (sortDescending ? ' ↓' : ' ↑')}
              </th>
            ))}
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('tickets.list.column.subject')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('tickets.list.column.department')}
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {rows.map((row) => (
            <tr key={row.uuid} className="hover:bg-gray-50">
              <td className="px-3 py-2">
                <input
                  type="checkbox"
                  aria-label={t('tickets.list.select_row', { reference: row.reference })}
                  checked={selected.has(row.uuid)}
                  onChange={() => onToggleRow(row.uuid)}
                />
              </td>
              <td className="px-3 py-2 font-medium text-gray-900">
                <Link to={`/tickets/${row.uuid}`} className="hover:underline">
                  {row.reference}
                </Link>
              </td>
              <td className="px-3 py-2">
                <span className={`inline-block rounded px-2 py-0.5 text-xs font-medium ${statusBadgeClass(row.status)}`}>
                  {t(statusLabelKey(row.status))}
                </span>
              </td>
              <td className="px-3 py-2">
                <span className={`inline-block rounded px-2 py-0.5 text-xs font-medium ${priorityBadgeClass(row.priority)}`}>
                  {t(priorityLabelKey(row.priority))}
                </span>
              </td>
              <td className="px-3 py-2 text-gray-600">{dateFormatter.format(new Date(row.updated_at))}</td>
              <td className="max-w-xs truncate px-3 py-2 text-gray-700" title={row.subject}>
                {row.subject}
              </td>
              <td className="px-3 py-2 text-gray-600">
                {row.department_id ? departmentNames.get(row.department_id) ?? '—' : '—'}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
