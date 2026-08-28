<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Controllers;

use App\Domains\Reporting\Exceptions\ReportExportTooLargeException;
use App\Domains\Reporting\Http\Requests\ReportQueryRequest;
use App\Domains\Reporting\Jobs\GenerateReportExportJob;
use App\Domains\Reporting\Models\ReportExport;
use App\Domains\Reporting\Models\ReportExportFormat;
use App\Domains\Reporting\Models\ReportExportState;
use App\Domains\Reporting\Services\Definitions\ReportRegistry;
use App\Domains\Reporting\Services\Export\ReportExporterRegistry;
use App\Domains\Reporting\Services\Scoping\ReportScopeResolver;
use App\Support\Http\ApiResponse;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ReportExportController extends Controller
{
    use AuthorizesRequests;

    public function store(
        ReportQueryRequest $request,
        string $report,
        ReportRegistry $reportRegistry,
        ReportExporterRegistry $exporterRegistry,
        ReportScopeResolver $scopeResolver,
    ): JsonResponse {
        $this->authorize('export', ReportExport::class);

        $definition = $reportRegistry->resolve($report);
        $filter = $request->toFilter();
        $resolved = $scopeResolver->resolve($request->user(), $report, $filter);

        // Count rows that would be exported
        $query = $definition->build($resolved['filter'], $resolved['scope']);
        $rowCount = count($query->rows);

        if ($rowCount > config('reporting.export.max_rows')) {
            throw ReportExportTooLargeException::for($rowCount, config('reporting.export.max_rows'));
        }

        $format = $request->input('format', 'csv');
        $exportFormat = ReportExportFormat::tryFrom($format);
        if (! $exportFormat) {
            throw new \InvalidArgumentException("Invalid format: {$format}");
        }

        // Check if we should render sync or async
        if ($rowCount <= config('reporting.export.sync_rows')) {
            // Render synchronously and stream
            $exporter = $exporterRegistry->resolve($exportFormat);
            $bytes = $exporter->render($query, $definition);

            return response($bytes, 200)
                ->header('Content-Type', $this->getContentType($exportFormat))
                ->header('Content-Disposition', "attachment; filename=\"report.{$exportFormat->value}\"");
        }

        // Create export record and dispatch job
        $export = ReportExport::create([
            'uuid' => (string) Str::uuid(),
            'report_key' => $report,
            'format' => $exportFormat->value,
            'filters' => $resolved['filter']->__toArray(),
            'requested_by_user_id' => $request->user()->id,
            'state' => ReportExportState::Pending->value,
            'row_count' => $rowCount,
            'expires_at' => CarbonImmutable::now()->addDays(config('reporting.export.retention_days')),
        ]);

        GenerateReportExportJob::dispatch($export);

        return ApiResponse::item(
            [
                'id' => $export->uuid,
                'state' => $export->state,
                'row_count' => $export->row_count,
            ],
            status: 202
        )->toResponse($request);
    }

    private function getContentType(ReportExportFormat $format): string
    {
        return match ($format) {
            ReportExportFormat::Csv => 'text/csv; charset=utf-8',
            ReportExportFormat::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ReportExportFormat::Pdf => 'application/pdf',
        };
    }
}
