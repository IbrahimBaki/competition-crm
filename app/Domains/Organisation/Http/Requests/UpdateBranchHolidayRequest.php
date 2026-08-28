<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBranchHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|array',
            'name.ar' => 'required_with:name|string|max:255',
            'name.en' => 'required_with:name|string|max:255',
            'date' => 'nullable|date_format:Y-m-d',
            'recurring_month_day' => 'nullable|regex:/^(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('date') && $this->filled('recurring_month_day')) {
                $validator->errors()->add('recurring_month_day', 'Only one of date or recurring_month_day may be set.');
            }
        });
    }
}
