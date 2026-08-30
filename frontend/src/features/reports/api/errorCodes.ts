/**
 * Map reporting-specific error codes to i18n keys.
 * Error codes come from app/Support/Http/Errors/ErrorCode.php
 * and are validated against lang/en/errors.php and lang/ar/errors.php
 */

export const reportingErrorCodeMap: Record<string, string> = {
  // Report not found
  'unknown_report': 'reports.export.unknown_report_error',

  // Date range exceeds max_range_days config
  'report_range_too_large': 'reports.filter.range_too_large_error',

  // Unsupported export format
  'unsupported_export_format': 'reports.export.unknown_format_error',

  // Export file too large
  'report_export_too_large': 'reports.export.too_large_error',

  // Export not ready (async export still running)
  'report_export_not_ready': 'reports.export.not_ready_error',

  // Export expired (retention_days exceeded)
  'report_export_expired': 'reports.export.expired_error',
};

/**
 * Resolve an error code to an i18n key.
 * Falls back to 'reports.export.unknown_format_error' if code is unmapped.
 */
export function resolveReportErrorCode(code: string): string {
  return reportingErrorCodeMap[code] || 'reports.export.unknown_format_error';
}
