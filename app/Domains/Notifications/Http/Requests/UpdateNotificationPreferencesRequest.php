<?php

namespace App\Domains\Notifications\Http\Requests;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationEventType;
use App\Domains\Notifications\Models\NotificationPreference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', NotificationPreference::class);
    }

    public function rules(): array
    {
        $eventTypes = array_map(fn ($case) => $case->value, NotificationEventType::cases());
        $channels = array_map(fn ($case) => $case->value, NotificationChannel::cases());

        return [
            'preferences' => ['required', 'array'],
            'preferences.*.event_type' => ['required', 'string', Rule::in($eventTypes)],
            'preferences.*.channel' => ['required', 'string', Rule::in($channels)],
            'preferences.*.enabled' => ['required', 'boolean'],
        ];
    }
}
