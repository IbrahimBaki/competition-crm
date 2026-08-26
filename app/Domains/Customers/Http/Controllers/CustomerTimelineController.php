<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Http\Resources\TimelineEntryResource;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\Timeline\BuildCustomerTimeline;
use DateTime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CustomerTimelineController extends Controller
{
    use AuthorizesRequests;

    public function index(Customer $customer, Request $request, BuildCustomerTimeline $timeline): JsonResponse
    {
        $this->authorize('view', $customer);

        $limit = min((int) $request->input('limit', 25), 100);
        $before = null;

        if ($request->has('before')) {
            try {
                $before = new DateTime($request->input('before'));
            } catch (\Exception) {
                abort(422, 'Invalid before parameter: must be ISO 8601 timestamp');
            }
        }

        $result = $timeline->execute($customer, $before, $limit);
        $entries = $result['entries'];
        $nextBefore = $result['next_before'];

        return response()->json([
            'data' => TimelineEntryResource::collection($entries),
            'meta' => [
                'next_before' => $nextBefore,
                'request_id' => request()->header('X-Request-ID') ?? Str::uuid(),
            ],
        ]);
    }
}
