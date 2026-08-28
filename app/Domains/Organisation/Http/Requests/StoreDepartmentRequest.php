<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|string|exists:branches,id|uuid',
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'code' => 'required|string|regex:/^[a-z0-9-]{2,32}$/|unique:departments,code,NULL,id,branch_id,'.$this->input('branch_id'),
        ];
    }
}
