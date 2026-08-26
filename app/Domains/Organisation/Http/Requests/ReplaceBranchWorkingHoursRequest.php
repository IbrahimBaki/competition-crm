<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceBranchWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days' => 'required|array|min:1|max:7',
            'days.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'days.*.is_working' => 'required|boolean',
            'days.*.opens_at' => 'required_if:days.*.is_working,true|nullable|date_format:H:i',
            'days.*.closes_at' => 'required_if:days.*.is_working,true|nullable|date_format:H:i|after:days.*.opens_at',
        ];
    }
}
