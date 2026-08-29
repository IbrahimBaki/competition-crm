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
        $parent = $request->input('parent_id') ? KnowledgeCategory::where('uuid', $request->input('parent_id'))->first() : null;
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
        $data = $request->validated();
        if (array_key_exists('parent_id', $data)) {
            $parent = $data['parent_id']
                ? KnowledgeCategory::where('uuid', $data['parent_id'])->firstOrFail()
                : null;
            $this->categoryTree->assertCanNest($parent);
            $data['parent_id'] = $parent?->id;
            $data['depth'] = ($parent?->depth ?? 0) + 1;
        }

        $category->update($data);

        return response()->json([
            'data' => new KnowledgeCategoryResource($category),
        ]);
    }
}
