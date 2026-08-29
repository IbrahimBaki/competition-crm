<?php

namespace App\Domains\Notifications\Http\Controllers;

use App\Domains\Notifications\Enums\NotificationState;
use App\Domains\Notifications\Http\Resources\NotificationDeliveryAttemptResource;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationDeliveryAttempt;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDeliveryController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewDeliveryLog', Notification::class);

        $query = NotificationDeliveryAttempt::query()
            ->whereHas('notification', fn ($q) => $q->where('state', NotificationState::Failed->value))
            ->orderBy('attempted_at', 'desc');

        $spec = (new CollectionQuerySpec)->withSorts(['attempted_at']);
        $collectionQuery = new CollectionQuery($request, $spec);
        $query = $collectionQuery->applyTo($query);
        $paginated = $collectionQuery->paginate($query);

        return ApiResponse::paginated(
            NotificationDeliveryAttemptResource::collection($paginated),
            $paginated,
        )->toResponse($request);
    }
}
