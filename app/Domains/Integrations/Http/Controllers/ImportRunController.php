<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Integrations\Models\ImportRun;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ImportRunController
{
    public function index(Request $request)
    {
        $runs = ImportRun::paginate();
        return ApiResponse::success($runs);
    }

    public function show(ImportRun $run)
    {
        return ApiResponse::success($run);
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
        return ApiResponse::success($run, status: 201);
    }
}
