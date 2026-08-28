<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|string|exists:departments,id|uuid',
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'code' => 'required|string|regex:/^[a-z0-9-]{2,32}$/|unique:teams,code,NULL,id,department_id,'.$this->input('department_id'),
        ];
    }
}
