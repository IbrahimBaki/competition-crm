<?php

namespace App\Domains\Ai\Http\Controllers;

use App\Domains\Ai\Models\AiUsageRecord;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class AiUsageController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', AiUsageRecord::class);

        $records = AiUsageRecord::paginate();

        return ApiResponse::collection($records);
    }
}
