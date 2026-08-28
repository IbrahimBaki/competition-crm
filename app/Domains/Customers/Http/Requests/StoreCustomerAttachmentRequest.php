<?php

namespace App\Domains\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attachment_uuid' => 'required|exists:attachments,uuid',
        ];
    }
}
