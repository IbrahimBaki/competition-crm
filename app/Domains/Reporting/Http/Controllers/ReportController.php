<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Controllers;

use App\Domains\Reporting\Http\Requests\ReportQueryRequest;
use App\Domains\Reporting\Http\Resources\ReportResultResource;
use App\Domains\Reporting\Policies\ReportPolicy;
use App\Domains\Reporting\Services\Definitions\ReportRegistry;
use App\Domains\Reporting\Services\Scoping\ReportScopeResolver;
use App\Support\Http\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    public function __construct(
        private ReportRegistry $registry,
        private ReportScopeResolver $scopeResolver,
        private ReportPolicy $policy,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorizeReport(request()->user());

        $definitions = [];
        foreach ($this->registry->all() as $key => $definition) {
            $definitions[] = [
                'key' => $definition->key(),
                'permission' => $definition->permission(),
                'columns' => $definition->columns(),
            ];
        }

        return ApiResponse::item($definitions, meta: [
            'available_reports' => count($definitions),
        ])->toResponse(request());
    }

    public function show(ReportQueryRequest $request, string $report): JsonResponse
    {
        // Authorize viewing this report type
        $this->authorizeReport($request->user(), $report);

        // Resolve the report definition
        $definition = $this->registry->resolve($report);

        // Build the filter from request
        $filter = $request->toFilter();

        // Resolve scoping
        $resolved = $this->scopeResolver->resolve(
            $request->user(),
            $report,
            $filter
        );

        // Build the report with scoped filter
        $result = $definition->build($resolved['filter'], $resolved['scope']);

        return ApiResponse::item(
            ReportResultResource::make($result),
            meta: ['report_key' => $report]
        )->toResponse($request);
    }

    private function authorizeReport(\App\Models\User $user, string $reportKey = ''): void
    {
        if (! $this->policy->viewAny($user, $reportKey)) {
            throw new AuthorizationException;
        }
    }
}
