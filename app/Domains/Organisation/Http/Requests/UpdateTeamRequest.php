<?php

namespace App\Domains\Organisation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teamId = $this->route('team')?->id;
        $departmentId = $this->route('team')?->department_id;

        return [
            'name' => 'array',
            'name.ar' => 'string|max:255',
            'name.en' => 'string|max:255',
            'code' => "string|regex:/^[a-z0-9-]{2,32}$/|unique:teams,code,{$teamId},id,department_id,{$departmentId}",
        ];
    }
}
