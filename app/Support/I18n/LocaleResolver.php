<?php

namespace App\Support\I18n;

use App\Models\User;
use Illuminate\Http\Request;

class LocaleResolver
{
    public const SUPPORTED = ['ar', 'en'];

    public function __construct(private LocalizationSettings $settings) {}

    public function resolve(Request $request, ?User $user): string
    {
        if ($locale = $this->parseAcceptLanguage($request)) {
            return $locale;
        }

        if ($user?->locale && in_array($user->locale, self::SUPPORTED)) {
            return $user->locale;
        }

        if ($locale = $this->settings->defaultLocale()) {
            return $locale;
        }

        return 'en';
    }

    private function parseAcceptLanguage(Request $request): ?string
    {
        $header = $request->header('Accept-Language');
        if (! $header) {
            return null;
        }

        $locales = array_map('trim', explode(',', $header));
        $parsed = [];

        foreach ($locales as $locale) {
            $parts = explode(';', $locale);
            $lang = trim($parts[0]);
            $q = 1.0;

            if (isset($parts[1])) {
                if (preg_match('/q=([0-9.]+)/', $parts[1], $matches)) {
                    $q = (float) $matches[1];
                }
            }

            $parsed[] = ['lang' => $lang, 'q' => $q];
        }

        usort($parsed, fn ($a, $b) => $b['q'] <=> $a['q']);

        foreach ($parsed as $item) {
            $lang = $item['lang'];
            if (in_array($lang, self::SUPPORTED)) {
                return $lang;
            }
            if (str_contains($lang, '-')) {
                $base = explode('-', $lang)[0];
                if (in_array($base, self::SUPPORTED)) {
                    return $base;
                }
            }
        }

        return null;
    }
}
