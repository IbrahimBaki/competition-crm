import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { CustomerListRow } from '../types';
import type { CustomerSortField } from './useCustomerListQuery';
import { CustomerStatusBadge } from '../detail/CustomerStatusBadge';

interface SortableColumn {
  field: CustomerSortField;
  labelKey: string;
}

// Only name/status/created_at are sortable server-side — see
// CustomerController::index()'s CollectionQuerySpec::withSorts().
const COLUMNS: SortableColumn[] = [
  { field: 'name', labelKey: 'customers.list.column.name' },
  { field: 'status', labelKey: 'customers.list.column.status' },
  { field: 'created_at', labelKey: 'customers.list.column.created_at' },
];

interface CustomerListTableProps {
  rows: CustomerListRow[];
  sort?: string;
  onSortChange: (sort: string) => void;
}

// Columns are deliberately limited to what CustomerController::index()
// actually returns (raw Eloquent columns, no CustomerResource wrapping —
// see .squad/gaps/34-481.md #7). Primary contact / company account /
// service tier are only available on the detail screen.
export function CustomerListTable({ rows, sort, onSortChange }: CustomerListTableProps) {
  const { t, i18n } = useTranslation();
  const dateFormatter = new Intl.DateTimeFormat(i18n.language, { dateStyle: 'medium' });

  const sortField = sort?.replace(/^-/, '');
  const sortDescending = sort?.startsWith('-') ?? false;

  const handleHeaderClick = (field: CustomerSortField) => {
    if (sortField === field) {
      onSortChange(sortDescending ? field : `-${field}`);
    } else {
      onSortChange(`-${field}`);
    }
  };

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
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
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {rows.map((row) => (
            <tr key={row.uuid} className="hover:bg-gray-50">
              <td className="px-3 py-2 font-medium text-gray-900">
                <Link to={`/customers/${row.uuid}`} className="hover:underline">
                  {row.name}
                </Link>
                {row.mergedIntoCustomerId !== null && (
                  <span className="ms-2 inline-block rounded bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">
                    {t('customers.list.merged_badge')}
                  </span>
                )}
              </td>
              <td className="px-3 py-2">
                <CustomerStatusBadge status={row.status} label={t(`customers.status.${row.status}`)} />
              </td>
              <td className="px-3 py-2 text-gray-600">{dateFormatter.format(new Date(row.createdAt))}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
