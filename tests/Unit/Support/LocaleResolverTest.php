<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocaleResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepts_ar_from_accept_language_header(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'ar',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('ar', $locale);
    }

    public function test_accepts_en_from_accept_language_header(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('en', $locale);
    }

    public function test_parses_quality_weighted_accept_language(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'en;q=0.5, ar;q=0.9',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('ar', $locale);
    }

    public function test_falls_back_to_unsupported_locale_in_accept_language(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr;q=0.9, ar;q=0.5',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('ar', $locale);
    }

    public function test_accepts_language_variant_like_ar_eg(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'ar-EG',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('ar', $locale);
    }

    public function test_falls_back_to_user_locale_when_no_header(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $request = Request::create('/');

        $locale = app(LocaleResolver::class)->resolve($request, $user);

        $this->assertEquals('en', $locale);
    }

    public function test_accept_language_header_takes_precedence(): void
    {
        $user = User::factory()->create(['locale' => 'ar']);
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, $user);

        $this->assertEquals('en', $locale);
    }

    public function test_complex_accept_language_header(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-CA,fr;q=0.9,en-US;q=0.3,en;q=0.2,ar;q=0.8',
        ]);

        $locale = app(LocaleResolver::class)->resolve($request, null);

        $this->assertEquals('ar', $locale);
    }
}
