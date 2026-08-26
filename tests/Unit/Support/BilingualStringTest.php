<?php

namespace Tests\Unit\Support;

use App\Support\I18n\BilingualString;
use PHPUnit\Framework\TestCase;

class BilingualStringTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => 'مرحبا', 'en' => 'Hello']);

        $this->assertEquals('مرحبا', $bilingual->ar);
        $this->assertEquals('Hello', $bilingual->en);
    }

    public function test_trims_whitespace(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => '  مرحبا  ', 'en' => '  Hello  ']);

        $this->assertEquals('مرحبا', $bilingual->ar);
        $this->assertEquals('Hello', $bilingual->en);
    }

    public function test_treats_empty_string_as_null(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => '', 'en' => '']);

        $this->assertNull($bilingual->ar);
        $this->assertNull($bilingual->en);
    }

    public function test_treats_whitespace_only_as_null(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => '   ', 'en' => "\t\n"]);

        $this->assertNull($bilingual->ar);
        $this->assertNull($bilingual->en);
    }

    public function test_for_locale_returns_requested_locale_when_present(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => 'مرحبا', 'en' => 'Hello']);

        $result = $bilingual->forLocale('ar');

        $this->assertEquals('مرحبا', $result['ar']);
        $this->assertEquals('Hello', $result['en']);
        $this->assertNull($result['__fallback']);
    }

    public function test_for_locale_falls_back_to_other_locale_when_requested_missing(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => null, 'en' => 'Hello']);

        $result = $bilingual->forLocale('ar');

        $this->assertEquals('Hello', $result['ar']);
        $this->assertEquals('Hello', $result['en']);
        $this->assertEquals('en', $result['__fallback']);
    }

    public function test_for_locale_en_falls_back_to_ar(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => 'مرحبا', 'en' => null]);

        $result = $bilingual->forLocale('en');

        $this->assertEquals('مرحبا', $result['ar']);
        $this->assertEquals('مرحبا', $result['en']);
        $this->assertEquals('ar', $result['__fallback']);
    }

    public function test_both_empty_returns_no_fallback(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => null, 'en' => null]);

        $result = $bilingual->forLocale('ar');

        $this->assertNull($result['ar']);
        $this->assertNull($result['en']);
        $this->assertNull($result['__fallback']);
    }

    public function test_to_array_returns_both_locales(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => 'مرحبا', 'en' => 'Hello']);

        $array = $bilingual->toArray();

        $this->assertEquals(['ar' => 'مرحبا', 'en' => 'Hello'], $array);
    }

    public function test_json_serialize_returns_both_locales(): void
    {
        $bilingual = BilingualString::fromArray(['ar' => 'مرحبا', 'en' => 'Hello']);

        $json = json_encode($bilingual, JSON_UNESCAPED_UNICODE);

        $this->assertEquals('{"ar":"مرحبا","en":"Hello"}', $json);
    }
}
