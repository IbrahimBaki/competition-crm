import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { requestExport } from '../api/wire';
import { resolveReportErrorCode } from '../api/errorCodes';
import type { ReportQuery, ExportFormat } from '../types';

interface ExportMenuProps {
  reportId: string;
  filters: Partial<ReportQuery>;
}

export function ExportMenu({ reportId, filters }: ExportMenuProps) {
  const { t } = useTranslation();
  const [showAck, setShowAck] = useState(false);

  const { mutate: export_report, isPending, error } = useMutation({
    mutationFn: (format: ExportFormat) =>
      requestExport(reportId, format, filters),
    onSuccess: (result) => {
      if (result.status === 'ready' && result.filename) {
        // Synchronous export: trigger download
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([], { type: result.contentType }));
        link.download = result.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else if (result.status === 'pending') {
        // Async export: show acknowledgement
        setShowAck(true);
      }
    },
  });

  const formats: ExportFormat[] = ['csv', 'xlsx', 'pdf'];
  const errorMessage = error ? t(resolveReportErrorCode((error as any)?.code || '')) : null;

  if (showAck) {
    return (
      <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div className="flex items-center gap-2">
          <span className="animate-spin">⏳</span>
          <div>
            <p className="font-medium text-blue-900">{t('reports.export.preparing')}</p>
            <p className="text-sm text-blue-700">{t('reports.export.ready_message')}</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-3">
      <div className="flex flex-wrap gap-2">
        {formats.map((format) => (
          <button
            key={format}
            onClick={() => export_report(format)}
            disabled={isPending}
            className="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isPending ? t('reports.export.exporting') : t(`reports.export.${format}_label`)}
          </button>
        ))}
      </div>

      {errorMessage && (
        <div className="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
          {errorMessage}
        </div>
      )}
    </div>
  );
}
