<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => 'array',
            'name.ar' => 'string|max:255',
            'name.en' => 'string|max:255',
            'code' => "string|regex:/^[a-z0-9-]{2,32}$/|unique:branches,code,{$branchId},id",
            'timezone' => 'timezone',
            'is_24_7' => 'boolean',
        ];
    }
}
