<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Integrations\Http\Resources\ImportRunResource;
use App\Domains\Integrations\Models\ImportRun;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ImportRunController
{
    public function index(Request $request)
    {
        $runs = ImportRun::paginate();

        // paginated() (not success()) so the response carries pagination meta,
        // and through the Resource so `id` is the uuid rather than the raw model.
        return ApiResponse::paginated(ImportRunResource::collection($runs), $runs);
    }

    public function show(ImportRun $run)
    {
        return ApiResponse::success(new ImportRunResource($run));
    }

    public function store(Request $request)
    {
        $run = ImportRun::create([
            'uuid' => (string) Str::uuid(),
            'kind' => $request->input('kind'),
            'mode' => $request->input('mode', 'dry_run'),
            'state' => 'pending',
            'created_by_user_id' => auth()->id(),
        ]);

        return ApiResponse::success(new ImportRunResource($run), status: 201);
    }
}
