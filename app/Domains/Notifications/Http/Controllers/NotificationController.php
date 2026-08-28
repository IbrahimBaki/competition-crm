<?php

namespace App\Domains\Notifications\Http\Controllers;

use App\Domains\Notifications\Http\Resources\NotificationResource;
use App\Domains\Notifications\Models\Notification;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController
{
    use AuthorizesRequests;

    public function index(Request $request, CollectionQuery $collectionQuery): JsonResponse
    {
        $this->authorize('viewAny', Notification::class);

        $query = Notification::query()
            ->where('recipient_user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        $spec = $collectionQuery->parseSpec($request->query());
        $paginated = $collectionQuery->apply($query, $spec);

        return ApiResponse::paginated(
            NotificationResource::collection($paginated),
            $paginated,
        );
    }

    public function markRead(Request $request, string $notificationUuid): JsonResponse
    {
        $notification = Notification::query()
            ->where('uuid', $notificationUuid)
            ->firstOrFail();

        $this->authorize('view', $notification);

        $notification->update(['read_at' => now()]);

        return ApiResponse::success(new NotificationResource($notification));
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Notification::class);

        Notification::query()
            ->where('recipient_user_id', $request->user()->id)
            ->where('read_at', null)
            ->update(['read_at' => now()]);

        return ApiResponse::success([
            'message' => 'All notifications marked as read',
        ]);
    }
}
