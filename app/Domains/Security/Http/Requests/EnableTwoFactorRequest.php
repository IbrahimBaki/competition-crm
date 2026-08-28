<?php

namespace App\Domains\Security\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnableTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
