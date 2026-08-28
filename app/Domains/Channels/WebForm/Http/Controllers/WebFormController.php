<?php

namespace App\Domains\Channels\WebForm\Http\Controllers;

use App\Domains\Channels\WebForm\Http\Requests\StoreWebFormRequest;
use App\Domains\Channels\WebForm\Http\Requests\UpdateWebFormRequest;
use App\Domains\Channels\WebForm\Http\Resources\WebFormResource;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class WebFormController
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', WebForm::class);

        $forms = WebForm::with('fields', 'department')->paginate();

        return ApiResponse::collection(WebFormResource::collection($forms))
            ->toResponse(request());
    }

    public function show(WebForm $webForm): JsonResponse
    {
        $this->authorize('view', $webForm);

        return ApiResponse::item(new WebFormResource($webForm))
            ->toResponse(request());
    }

    public function store(StoreWebFormRequest $request): JsonResponse
    {
        $this->authorize('create', WebForm::class);

        $webForm = WebForm::create([
            'key' => $request->input('key'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'department_id' => $request->input('department_id'),
            'ticket_category_id' => $request->input('ticket_category_id'),
            'default_priority' => $request->input('default_priority'),
            'acknowledgement_template_key' => $request->input('acknowledgement_template_key'),
            'is_active' => false,
        ]);

        return ApiResponse::item(new WebFormResource($webForm), 201)
            ->toResponse(request());
    }

    public function update(WebForm $webForm, UpdateWebFormRequest $request): JsonResponse
    {
        $this->authorize('update', $webForm);

        $webForm->update([
            'title' => $request->input('title', $webForm->title),
            'description' => $request->input('description', $webForm->description),
            'department_id' => $request->input('department_id', $webForm->department_id),
            'ticket_category_id' => $request->input('ticket_category_id', $webForm->ticket_category_id),
            'default_priority' => $request->input('default_priority', $webForm->default_priority),
            'acknowledgement_template_key' => $request->input('acknowledgement_template_key', $webForm->acknowledgement_template_key),
            'is_active' => $request->boolean('is_active', $webForm->is_active),
        ]);

        return ApiResponse::item(new WebFormResource($webForm))
            ->toResponse(request());
    }

    public function destroy(WebForm $webForm): JsonResponse
    {
        $this->authorize('delete', $webForm);

        $webForm->delete();

        return ApiResponse::noContent()->toResponse(request());
    }
}
