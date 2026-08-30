import type { ReportFilterDescriptor } from '../types';

/**
 * Global filter descriptors for all reports.
 * These mirror the accepted parameters in ReportQueryRequest.php:25-39.
 * TODO(gap-484): Ideally these would come from ReportDefinitionResource,
 * but it currently returns only key/permission/columns.
 */
export const reportFilterDescriptors: ReportFilterDescriptor[] = [
  {
    key: 'date_from',
    type: 'dateRange',
    required: true,
  },
  {
    key: 'date_to',
    type: 'dateRange',
    required: true,
  },
  {
    key: 'timezone',
    type: 'select',
    required: false,
    // Options are loaded dynamically from the browser (Intl API)
  },
  {
    key: 'branch',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
  {
    key: 'department',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
  {
    key: 'team',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
  {
    key: 'agent',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
  {
    key: 'category',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
  {
    key: 'priority',
    type: 'select',
    required: false,
    options: [
      { value: 'low', label: 'Low' },
      { value: 'normal', label: 'Normal' },
      { value: 'high', label: 'High' },
      { value: 'urgent', label: 'Urgent' },
    ],
  },
  {
    key: 'channel',
    type: 'select',
    required: false,
    options: [
      { value: 'email', label: 'Email' },
      { value: 'chat', label: 'Chat' },
      { value: 'phone', label: 'Phone' },
      { value: 'portal', label: 'Portal' },
    ],
  },
  {
    key: 'tag',
    type: 'select',
    required: false,
    // Options are populated from the API
  },
];

/**
 * Get a filter descriptor by key.
 */
export function getFilterDescriptor(key: string): ReportFilterDescriptor | undefined {
  return reportFilterDescriptors.find((d) => d.key === key);
}
