<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\AttachFileToCustomer;
use App\Domains\Customers\Http\Requests\StoreCustomerAttachmentRequest;
use App\Domains\Customers\Http\Resources\CustomerAttachmentResource;
use App\Domains\Customers\Models\Customer;
use App\Support\Attachments\Attachment;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Customer $customer, Request $request)
    {
        $this->authorize('viewAny', Attachment::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['created_at', '-created_at'])
            ->withIncludes([]);

        $query = new CollectionQuery($request, $spec);
        $attachments = $query->paginate($customer->attachments());

        return ApiResponse::collection(
            $attachments,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function store(
        Customer $customer,
        StoreCustomerAttachmentRequest $request,
        AttachFileToCustomer $action,
    ) {
        $this->authorize('create', Attachment::class);

        $attachment = Attachment::where('uuid', $request->input('attachment_uuid'))->firstOrFail();

        $attachment = $action->execute($customer, $attachment, $request->user());

        return ApiResponse::item(new CustomerAttachmentResource($attachment), 201);
    }

    public function destroy(Customer $customer, Attachment $attachment): JsonResponse
    {
        if ($attachment->attachable_id !== $customer->id) {
            abort(404);
        }

        $this->authorize('delete', $attachment);

        $attachment->attachable_type = null;
        $attachment->attachable_id = null;
        $attachment->save();

        return ApiResponse::noContent();
    }
}
