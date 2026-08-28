<?php

namespace App\Domains\Knowledge\Http\Controllers;

use App\Domains\Knowledge\Http\Requests\StoreKnowledgeCategoryRequest;
use App\Domains\Knowledge\Http\Requests\UpdateKnowledgeCategoryRequest;
use App\Domains\Knowledge\Http\Resources\KnowledgeCategoryResource;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Knowledge\Services\KnowledgeCategoryTree;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class KnowledgeCategoryController
{
    use AuthorizesRequests;

    public function __construct(
        private KnowledgeCategoryTree $categoryTree,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', KnowledgeCategory::class);

        $tree = $this->categoryTree->getActiveTree(Audience::Staff);

        return response()->json([
            'data' => $tree,
        ]);
    }

    public function store(StoreKnowledgeCategoryRequest $request): JsonResponse
    {
        $parent = $request->input('parent_id') ? KnowledgeCategory::find($request->input('parent_id')) : null;
        $this->categoryTree->assertCanNest($parent);

        $category = KnowledgeCategory::create([
            'uuid' => Str::uuid(),
            'parent_id' => $parent?->id,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'depth' => ($parent?->depth ?? 0) + 1,
            'position' => $request->input('position', 0),
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new KnowledgeCategoryResource($category),
        ], 201);
    }

    public function update(UpdateKnowledgeCategoryRequest $request, KnowledgeCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'data' => new KnowledgeCategoryResource($category),
        ]);
    }
}
