<?php

namespace App\Domains\Channels\Messaging\Services\Templates;

use App\Domains\Channels\Messaging\Exceptions\ProviderTemplateNotApprovedException;
use App\Domains\Channels\Messaging\Exceptions\ProviderTemplateVariableMissingException;
use App\Domains\Channels\Messaging\Models\ProviderMessageTemplate;
use App\Support\I18n\LocalizationSettings;

final class ProviderTemplateRenderer
{
    public function __construct(
        private readonly LocalizationSettings $localizationSettings,
    ) {}

    public function render(
        ProviderMessageTemplate $template,
        array $variables,
        string $locale,
    ): string {
        if ($template->approved_at === null || ! $template->is_active) {
            throw new ProviderTemplateNotApprovedException;
        }

        $resolvedLocale = $this->resolveLocale($locale);
        $body = $resolvedLocale === 'ar' ? $template->body_ar : $template->body_en;

        return $this->substituteVariables($body, $variables, $template->variables);
    }

    private function resolveLocale(string $recipientLocale): string
    {
        if (in_array($recipientLocale, ['ar', 'en'], strict: true)) {
            return $recipientLocale;
        }

        return $this->localizationSettings->defaultLocale();
    }

    private function substituteVariables(string $body, array $variables, array $declared): string
    {
        foreach ($declared as $variable) {
            if (! isset($variables[$variable])) {
                throw new ProviderTemplateVariableMissingException($variable);
            }
        }

        return preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            function ($matches) use ($variables) {
                $key = $matches[1];

                return (string) ($variables[$key] ?? '');
            },
            $body
        );
    }
}
