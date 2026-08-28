<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Http\Resources\PortalAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalAccountController
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new PortalAccountResource($request->user('portal')),
        ]);
    }
}
