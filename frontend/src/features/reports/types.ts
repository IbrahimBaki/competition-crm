export type ReportId = string;

/**
 * A report definition describes which filters it accepts, its columns, and the permission required.
 * Column labels and numeric flags come from i18n keys: reports.<reportKey>.column.<columnKey>.label/numeric
 */
export interface ReportDefinition {
  key: ReportId;
  permission: string;
  columns: string[]; // Column keys only; labels are resolved from i18n
}

/**
 * A filter descriptor defines how to render a single filter control.
 * All reports accept the same global set of filters from ReportQueryRequest.
 * This drives the shared ReportFilterBar.
 */
export interface ReportFilterDescriptor {
  key: string;
  type: 'dateRange' | 'select' | 'multiSelect' | 'boolean';
  required: boolean;
  options?: Array<{ value: string; label: string }>;
}

/**
 * Report query parameters as accepted by the backend.
 * See app/Domains/Reporting/Http/Requests/ReportQueryRequest.php:25-39
 */
export interface ReportQuery {
  date_from: string; // Format: Y-m-d (e.g., "2026-08-28")
  date_to: string; // Format: Y-m-d
  timezone?: string; // IANA timezone string; defaults to server config
  branch?: string; // UUID
  department?: string; // UUID
  team?: string; // UUID
  agent?: string; // UUID
  category?: string; // UUID
  priority?: 'low' | 'normal' | 'high' | 'urgent';
  channel?: 'email' | 'chat' | 'phone' | 'portal';
  tag?: string; // UUID
}

export interface ReportResult {
  generated_at: string; // ISO8601 datetime
  timezone: string;
  rows: Array<Record<string, unknown>>;
  totals?: Record<string, unknown>; // For dashboard-style aggregations
}

/**
 * Column metadata resolved from i18n.
 * Not returned by the API; derived per-report from i18n keys.
 */
export interface ReportColumnMetadata {
  key: string;
  label: string;
  numeric: boolean;
}

export type ExportFormat = 'csv' | 'xlsx' | 'pdf';

export interface ReportExportRequest {
  format: ExportFormat;
}

/**
 * Synchronous export response (rows <= sync_rows threshold).
 * The raw file bytes are returned as binary attachment.
 */
export interface ReportExportSync {
  status: 'ready';
  contentType: string;
  filename: string;
}

/**
 * Asynchronous export response (rows > sync_rows threshold).
 * The export is queued and will be generated in the background.
 * TODO(gap-484): No status/download route exists; export is fire-and-forget.
 */
export interface ReportExportAsync {
  id: string;
  state: 'pending' | 'running' | 'ready' | 'failed';
  row_count: number;
  expires_at?: string;
}

export type ReportExportResponse = ReportExportSync | ReportExportAsync;
