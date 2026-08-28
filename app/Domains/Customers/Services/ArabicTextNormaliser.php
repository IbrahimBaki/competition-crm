<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Models\ContactType;

class ArabicTextNormaliser implements TextNormaliser
{
    public function normaliseName(string $value): string
    {
        // Unicode NFKC normalise
        $normalized = $this->normalizeUnicode($value);

        // Lowercase
        $normalized = mb_strtolower($normalized, 'UTF-8');

        // Strip Arabic diacritics (harakat U+064B–U+0652, U+0670, tatweel U+0640).
        // PCRE spells code points \x{...}; \u{...} is a PHP double-quoted-string
        // escape and makes this pattern fail to compile.
        $normalized = preg_replace('/[\x{064B}-\x{0652}\x{0670}\x{0640}]/u', '', $normalized);

        // Fold alef variants (آ أ إ ٱ) -> ا
        $normalized = str_replace(['آ', 'أ', 'إ', 'ٱ'], 'ا', $normalized);

        // Fold taa marbuta (ة) -> ه
        $normalized = str_replace('ة', 'ه', $normalized);

        // Fold alef maqsura (ى) -> ي
        $normalized = str_replace('ى', 'ي', $normalized);

        // Fold hamza carriers (ؤ ئ) -> ء
        $normalized = str_replace(['ؤ', 'ئ'], 'ء', $normalized);

        // Collapse whitespace and trim
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        return $normalized;
    }

    public function normaliseContact(string $value, ContactType $type): string
    {
        if ($type->isPhoneLike()) {
            return $this->normalisePhoneE164($value);
        }

        return match ($type) {
            ContactType::Email, ContactType::PortalLogin, ContactType::Chat => mb_strtolower(trim($value), 'UTF-8'),
            default => '',
        };
    }

    private function normalisePhoneE164(string $value): string
    {
        // Convert Arabic-Indic digits to ASCII
        $value = $this->foldArabicIndic($value);

        // Keep only digits and leading +
        $value = preg_replace('/[^\d+]/', '', $value);

        if (empty($value)) {
            return '';
        }

        // If already international format (starts with +)
        if ($value[0] === '+') {
            return $value;
        }

        // Handle leading 00 (international format without +)
        if (str_starts_with($value, '00')) {
            return '+'.substr($value, 2);
        }

        // Handle leading 0 (domestic format with trunk prefix)
        if ($value[0] === '0') {
            $value = substr($value, 1);
        }

        // Add country code for domestic format
        $countryCode = config('customers.identity.default_country_code', '+966');
        $countryCode = ltrim($countryCode, '+');

        return "+{$countryCode}{$value}";
    }

    private function foldArabicIndic(string $value): string
    {
        // Arabic-Indic digits: ٠-٩ (U+0660-U+0669) -> 0-9
        $arabicIndic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabicIndic, $latin, $value);
    }

    private function normalizeUnicode(string $value): string
    {
        if (function_exists('normalizer_normalize')) {
            return normalizer_normalize($value, \Normalizer::FORM_KC);
        }

        return $value;
    }
}
