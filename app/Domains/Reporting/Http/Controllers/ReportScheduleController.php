<?php

namespace App\Domains\Reporting\Http\Controllers;

use App\Domains\Reporting\Http\Requests\StoreReportScheduleRequest;
use App\Domains\Reporting\Http\Requests\UpdateReportScheduleRequest;
use App\Domains\Reporting\Http\Resources\ReportScheduleResource;
use App\Domains\Reporting\Models\ReportSchedule;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class ReportScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', ReportSchedule::class);

        return ApiResponse::collection(ReportSchedule::paginate());
    }

    public function store(StoreReportScheduleRequest $request)
    {
        $this->authorize('create', ReportSchedule::class);

        $schedule = ReportSchedule::create($request->validated());

        return ApiResponse::created(new ReportScheduleResource($schedule));
    }

    public function show(ReportSchedule $schedule)
    {
        $this->authorize('view', $schedule);

        return ApiResponse::ok(new ReportScheduleResource($schedule));
    }

    public function update(UpdateReportScheduleRequest $request, ReportSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $schedule->update($request->validated());

        return ApiResponse::ok(new ReportScheduleResource($schedule));
    }

    public function destroy(ReportSchedule $schedule)
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return ApiResponse::noContent();
    }
}
