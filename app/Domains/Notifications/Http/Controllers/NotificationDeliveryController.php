<?php

namespace App\Domains\Notifications\Http\Controllers;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Http\Resources\NotificationDeliveryAttemptResource;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationDeliveryAttempt;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDeliveryController
{
    use AuthorizesRequests;

    public function index(Request $request, CollectionQuery $collectionQuery): JsonResponse
    {
        $this->authorize('viewDeliveryLog', Notification::class);

        $query = NotificationDeliveryAttempt::query()
            ->whereHas('notification', fn ($q) => $q->where('state', NotificationState::Failed->value))
            ->orderBy('attempted_at', 'desc');

        $spec = $collectionQuery->parseSpec($request->query());
        $paginated = $collectionQuery->apply($query, $spec);

        return ApiResponse::paginated(
            NotificationDeliveryAttemptResource::collection($paginated),
            $paginated,
        );
    }
}
