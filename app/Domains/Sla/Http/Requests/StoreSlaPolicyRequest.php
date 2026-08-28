<?php

namespace App\Domains\Sla\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlaPolicyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'targets' => ['required', 'array', 'min:1'],
            'targets.*.type' => ['required', 'in:first_response,resolution'],
            'targets.*.minutes' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
