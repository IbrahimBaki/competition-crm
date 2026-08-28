<?php

namespace App\Domains\Sla\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSlaPolicyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'targets' => ['sometimes', 'array', 'min:1'],
            'targets.*.type' => ['required_with:targets', 'in:first_response,resolution'],
            'targets.*.minutes' => ['required_with:targets', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
