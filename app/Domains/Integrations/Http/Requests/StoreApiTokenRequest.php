<?php

namespace App\Domains\Integrations\Http\Requests;

use App\Domains\Integrations\Models\ApiScope;
use App\Domains\Integrations\Models\ApiToken;
use Illuminate\Foundation\Http\FormRequest;

final class StoreApiTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ApiToken::class);
    }

    public function rules(): array
    {
        $scopes = implode(',', array_map(fn ($case) => $case->value, ApiScope::cases()));

        return [
            'name' => 'required|string|max:255',
            'scopes' => 'required|array|min:1',
            'scopes.*' => "in:{$scopes}",
        ];
    }
}
