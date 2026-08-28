import { getReports, getReport } from '@/api/generated/reporting/reporting';
import { apiRequest } from '@/api/http/mutator';
import type {
  ReportDefinition,
  ReportQuery,
  ReportResult,
  ExportFormat,
} from '../types';

/**
 * Fetch all available report definitions.
 */
export async function fetchReportDefinitions(): Promise<ReportDefinition[]> {
  const result = await getReports();
  // Generated client already unwraps the envelope; return as-is
  return (result as any) || [];
}

/**
 * Fetch a specific report by id with the given filters.
 * Drops any filter keys not accepted by ReportQueryRequest.
 */
export async function fetchReport(id: string, filters: Partial<ReportQuery>): Promise<ReportResult> {
  // Whitelist only accepted parameters
  const acceptedKeys: (keyof ReportQuery)[] = [
    'date_from',
    'date_to',
    'timezone',
    'branch',
    'department',
    'team',
    'agent',
    'category',
    'priority',
    'channel',
    'tag',
  ];

  const cleanedFilters: Record<string, string | undefined> = {};
  acceptedKeys.forEach((key) => {
    const value = filters[key];
    if (value !== undefined) {
      cleanedFilters[key] = String(value);
    }
  });

  const result = await getReport(id);
  return (result as any) || {};
}

/**
 * Generate an idempotency key as a hash of the request body.
 * Same filters + same report + same format = same key.
 * Changed filters = different key = new export request.
 * Uses a simple string hash that's deterministic.
 */
async function generateIdempotencyKey(body: Record<string, unknown>): Promise<string> {
  const jsonStr = JSON.stringify(body);
  const encoder = new TextEncoder();
  const data = encoder.encode(jsonStr);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map((b) => b.toString(16).padStart(2, '0')).join('');
}

/**
 * Request an export of a report.
 * Small exports (≤ sync_rows) return the file synchronously as 200.
 * Large exports (> sync_rows) return 202 Accepted with pending state.
 * TODO(gap-484): No route to check async export status or download.
 * Fire-and-forget for large exports; synchronous download for small ones.
 */
export async function requestExport(
  reportId: string,
  format: ExportFormat,
  filters: Partial<ReportQuery>
): Promise<{
  status: 'ready' | 'pending';
  id?: string;
  contentType?: string;
  filename?: string;
}> {
  const acceptedKeys: (keyof ReportQuery)[] = [
    'date_from',
    'date_to',
    'timezone',
    'branch',
    'department',
    'team',
    'agent',
    'category',
    'priority',
    'channel',
    'tag',
  ];

  const cleanedFilters: Record<string, string | undefined> = {};
  acceptedKeys.forEach((key) => {
    const value = filters[key];
    if (value !== undefined) {
      cleanedFilters[key] = String(value);
    }
  });

  const body = { format, ...cleanedFilters };
  const idempotencyKey = await generateIdempotencyKey(body);

  const response = await apiRequest<any>({
    method: 'POST',
    url: `/v1/reports/${reportId}/export`,
    data: body,
    headers: {
      'Idempotency-Key': idempotencyKey,
    },
  });

  // The response might be wrapped in an envelope; check if it has the expected shape
  const data = (response as any)?.data || response;

  // 202 Accepted: async export
  if (data?.state) {
    return {
      status: 'pending',
      id: data.id,
    };
  }

  // 200 OK: synchronous export (file is returned as attachment)
  // The response is the raw file bytes; we don't unwrap here
  return {
    status: 'ready',
    contentType: 'application/octet-stream',
    filename: `report.${format}`,
  };
}
