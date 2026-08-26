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

        // Strip Arabic diacritics (harakat U+064B–U+0652, U+0670, tatweel U+0640)
        $normalized = preg_replace('/[\u{064B}-\u{0652}\u{0670}\u{0640}]/u', '', $normalized);

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
        return match ($type) {
            ContactType::Email, ContactType::PortalLogin => mb_strtolower(trim($value), 'UTF-8'),
            ContactType::Phone, ContactType::Whatsapp => $this->normalisePhoneNumber($value),
        };
    }

    private function normalisePhoneNumber(string $value): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $value);

        // Drop leading 00 or + country prefix marker
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    private function normalizeUnicode(string $value): string
    {
        if (function_exists('normalizer_normalize')) {
            return normalizer_normalize($value, \Normalizer::FORM_KC);
        }

        return $value;
    }
}
