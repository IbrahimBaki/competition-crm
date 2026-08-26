<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferTicketToDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transferToDepartment', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'string', 'exists:departments,id'],
            'keep_assignee' => ['nullable', 'boolean'],
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
