<?php

namespace App\Support\I18n;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LocalizationSettings
{
    public function defaultLocale(): string
    {
        return Cache::remember(
            'localization:default_locale',
            now()->addDay(),
            function () {
                return DB::table('auth_settings')
                    ->where('id', 1)
                    ->value('default_locale') ?? 'ar';
            }
        );
    }

    public function setDefaultLocale(string $locale): void
    {
        Cache::forget('localization:default_locale');
        DB::table('auth_settings')
            ->where('id', 1)
            ->update(['default_locale' => $locale]);
    }
}
