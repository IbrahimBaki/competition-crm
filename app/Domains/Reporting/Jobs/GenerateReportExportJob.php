<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Jobs;

use App\Domains\Reporting\Models\ReportExport;
use App\Domains\Reporting\Models\ReportExportFormat;
use App\Domains\Reporting\Models\ReportExportState;
use App\Domains\Reporting\Services\Definitions\ReportRegistry;
use App\Domains\Reporting\Services\Export\ReportExporterRegistry;
use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Support\Attachments\AttachmentStorage;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 600;

    public function __construct(
        public ReportExport $export,
    ) {}

    public function handle(
        ReportRegistry $reportRegistry,
        ReportExporterRegistry $exporterRegistry,
        AttachmentStorage $attachmentStorage,
    ): void {
        try {
            $this->export->update(['state' => ReportExportState::Running->value]);

            $definition = $reportRegistry->resolve($this->export->report_key);
            $format = ReportExportFormat::from($this->export->format);

            // Reconstruct filter from JSON
            $filterData = $this->export->filters;
            $filter = new ReportFilter(
                from: CarbonImmutable::parse($filterData['from']),
                to: CarbonImmutable::parse($filterData['to']),
                timezone: $filterData['timezone'],
                branchId: $filterData['branchId'] ?? null,
                departmentId: $filterData['departmentId'] ?? null,
                teamId: $filterData['teamId'] ?? null,
                agentId: $filterData['agentId'] ?? null,
                categoryId: $filterData['categoryId'] ?? null,
                priority: $filterData['priority'] ?? null,
                channel: $filterData['channel'] ?? null,
                tagId: $filterData['tagId'] ?? null,
            );

            // Build report
            $result = $definition->build($filter, fn ($q) => $q);

            // Render to format
            $exporter = $exporterRegistry->resolve($format);
            $bytes = $exporter->render($result, $definition);

            // Store attachment
            $attachment = $attachmentStorage->store(
                name: "report-{$this->export->report_key}.{$format->value}",
                contents: $bytes,
                mimeType: $this->getMimeType($format),
                metadata: [
                    'export_id' => $this->export->id,
                    'report_key' => $this->export->report_key,
                ]
            );

            // Update export as ready
            $this->export->update([
                'state' => ReportExportState::Ready->value,
                'attachment_id' => $attachment->id,
            ]);

            // TODO: Dispatch notification

        } catch (\Throwable $e) {
            $this->failed($e);
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->export->update([
            'state' => ReportExportState::Failed->value,
            'failure_code' => class_basename($e),
        ]);
    }

    private function getMimeType(ReportExportFormat $format): string
    {
        return match ($format) {
            ReportExportFormat::Csv => 'text/csv',
            ReportExportFormat::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ReportExportFormat::Pdf => 'application/pdf',
        };
    }
}
