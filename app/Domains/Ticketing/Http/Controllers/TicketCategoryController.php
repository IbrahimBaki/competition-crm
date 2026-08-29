<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Actions\CreateTicketCategory;
use App\Domains\Ticketing\Actions\UpdateTicketCategory;
use App\Domains\Ticketing\Http\Resources\TicketCategoryResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TicketCategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $categories = TicketCategory::with('children', 'fields')->whereNull('parent_id')->paginate(100);

        return ApiResponse::paginated(
            TicketCategoryResource::collection($categories),
            $categories,
        )->toResponse(request());
    }

    public function store(CreateTicketCategory $action): JsonResponse
    {
        $this->authorize('create', TicketCategory::class);

        $category = $action->handle(
            code: request()->input('code'),
            name: request()->input('name'),
            parent: request()->filled('parent_uuid')
                ? TicketCategory::where('uuid', request()->input('parent_uuid'))->first()
                : null,
        );

        return ApiResponse::item(new TicketCategoryResource($category), 201)
            ->toResponse(request());
    }

    public function update(TicketCategory $category, UpdateTicketCategory $action): JsonResponse
    {
        $this->authorize('update', $category);

        $updated = $action->handle($category);

        return ApiResponse::item(new TicketCategoryResource($updated))
            ->toResponse(request());
    }

    public function destroy(TicketCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);

        if (Ticket::where('ticket_category_id', $category->id)->exists()) {
            return response()->json([
                'error' => 'Category has open tickets',
            ], 409);
        }

        $category->delete();

        return ApiResponse::noContent()->toResponse(request());
    }
}
