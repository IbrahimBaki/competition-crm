<?php

namespace App\Support\Http\Uploads\Requests;

use App\Support\Http\Uploads\AttachmentRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => AttachmentRules::file(),
        ];
    }
}
