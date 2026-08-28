<?php

namespace App\Domains\Channels\Chat\Http\Requests;

use App\Domains\Channels\Chat\Models\ChatTransferTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferChatSessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', Rule::enum(ChatTransferTargetType::class)],
            'target_agent_uuid' => ['required_if:target_type,agent', 'nullable', 'uuid', 'exists:users,uuid'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
