<?php

namespace App\Domains\Workspace\Http\Requests;

use App\Domains\Workspace\Models\QuickReply;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuickReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', QuickReply::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|array',
            'title.ar' => 'required|string|max:255',
            'title.en' => 'required|string|max:255',
            'body' => 'required|array',
            'body.ar' => 'required|string',
            'body.en' => 'required|string',
            'scope' => 'required|in:personal,shared',
            'department_id' => 'nullable|uuid|exists:departments,id',
        ];
    }
}
