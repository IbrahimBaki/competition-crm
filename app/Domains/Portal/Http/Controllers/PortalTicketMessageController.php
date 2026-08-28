<?php

namespace App\Domains\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalTicketMessageController
{
    public function index(Request $request, string $ticket): JsonResponse
    {
        return response()->json(['data' => []], 200);
    }

    public function store(Request $request, string $ticket): JsonResponse
    {
        return response()->json(['message' => 'Not implemented yet'], 501);
    }
}
