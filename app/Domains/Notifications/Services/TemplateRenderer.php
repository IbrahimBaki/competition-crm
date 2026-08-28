<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationEventType;
use App\Domains\Notifications\Exceptions\NotificationTemplateMissingException;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Support\I18n\LocalizationSettings;
use Illuminate\Support\Arr;

final class TemplateRenderer
{
    public function __construct(
        private LocalizationSettings $localizationSettings,
    ) {}

    public function render(
        NotificationEventType $eventType,
        string $channel,
        string $recipientLocale,
        array $payload,
    ): array {
        $template = NotificationTemplate::query()
            ->where('code', $eventType->value)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            throw new NotificationTemplateMissingException($eventType->value, $channel);
        }

        $locale = $this->resolveLocale($recipientLocale);
        $templateData = $template->forLocale($locale);

        $subject = $this->substituteTokens($templateData['subject'] ?? '', $payload);
        $body = $this->substituteTokens($templateData['body'] ?? '', $payload);

        return [
            'subject' => $subject,
            'body' => $body,
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
        ];
    }

    private function resolveLocale(string $recipientLocale): string
    {
        if (in_array($recipientLocale, ['ar', 'en'])) {
            return $recipientLocale;
        }

        return $this->localizationSettings->defaultLocale();
    }

    private function substituteTokens(string $text, array $payload): string
    {
        return preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            function ($matches) use ($payload) {
                $key = $matches[1];
                $value = Arr::get($payload, $key);

                if ($value === null) {
                    \Log::warning("Notification placeholder not found: {$key}");

                    return '';
                }

                return (string) $value;
            },
            $text
        );
    }
}
