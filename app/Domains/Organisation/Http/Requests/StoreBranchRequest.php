<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'code' => 'required|string|regex:/^[a-z0-9-]{2,32}$/|unique:branches,code',
            'timezone' => 'required|timezone',
            'is_24_7' => 'boolean',
        ];
    }
}
