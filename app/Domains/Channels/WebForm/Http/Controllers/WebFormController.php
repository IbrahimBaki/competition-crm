<?php

namespace App\Domains\Channels\WebForm\Http\Controllers;

use App\Domains\Channels\WebForm\Http\Requests\StoreWebFormRequest;
use App\Domains\Channels\WebForm\Http\Requests\UpdateWebFormRequest;
use App\Domains\Channels\WebForm\Http\Resources\WebFormResource;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class WebFormController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', WebForm::class);

        $forms = WebForm::with('fields', 'department')->paginate();

        return ApiResponse::collection(WebFormResource::collection($forms))
            ->toResponse(request());
    }

    public function show(WebForm $form): JsonResponse
    {
        $this->authorize('view', $form);

        return ApiResponse::item(new WebFormResource($form))
            ->toResponse(request());
    }

    public function store(StoreWebFormRequest $request): JsonResponse
    {
        $this->authorize('create', WebForm::class);

        $form = WebForm::create([
            'key' => $request->input('key'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'department_id' => $request->input('department_id'),
            'ticket_category_id' => $request->input('ticket_category_id'),
            'default_priority' => $request->input('default_priority'),
            'acknowledgement_template_key' => $request->input('acknowledgement_template_key'),
            'is_active' => false,
        ]);

        return ApiResponse::item(new WebFormResource($form), 201)
            ->toResponse(request());
    }

    public function update(WebForm $form, UpdateWebFormRequest $request): JsonResponse
    {
        $this->authorize('update', $form);

        $form->update([
            'title' => $request->input('title', $form->title),
            'description' => $request->input('description', $form->description),
            'department_id' => $request->input('department_id', $form->department_id),
            'ticket_category_id' => $request->input('ticket_category_id', $form->ticket_category_id),
            'default_priority' => $request->input('default_priority', $form->default_priority),
            'acknowledgement_template_key' => $request->input('acknowledgement_template_key', $form->acknowledgement_template_key),
            'is_active' => $request->boolean('is_active', $form->is_active),
        ]);

        return ApiResponse::item(new WebFormResource($form))
            ->toResponse(request());
    }

    public function destroy(WebForm $form): JsonResponse
    {
        $this->authorize('delete', $form);

        $form->delete();

        return ApiResponse::noContent()->toResponse(request());
    }
}
