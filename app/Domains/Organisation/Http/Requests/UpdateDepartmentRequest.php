<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;
        $branchId = $this->route('department')?->branch_id;

        return [
            'name' => 'array',
            'name.ar' => 'string|max:255',
            'name.en' => 'string|max:255',
            'code' => "string|regex:/^[a-z0-9-]{2,32}$/|unique:departments,code,{$departmentId},id,branch_id,{$branchId}",
        ];
    }
}
