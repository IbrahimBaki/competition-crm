<?php

namespace App\Support\I18n;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class BilingualString implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly ?string $ar,
        public readonly ?string $en,
    ) {}

    public static function fromArray(array $value): self
    {
        $ar = is_string($value['ar'] ?? null) ? trim($value['ar']) : null;
        $en = is_string($value['en'] ?? null) ? trim($value['en']) : null;

        return new self(
            ar: empty($ar) ? null : $ar,
            en: empty($en) ? null : $en,
        );
    }

    public function forLocale(string $locale): array
    {
        $requested = $locale === 'ar' ? 'ar' : 'en';
        $fallback = $locale === 'ar' ? 'en' : 'ar';

        $result = [
            'ar' => $this->ar,
            'en' => $this->en,
        ];

        if ($this->{$requested} !== null) {
            return [...$result, '__fallback' => null];
        }

        if ($this->{$fallback} !== null) {
            $result[$requested] = $this->{$fallback};

            return [...$result, '__fallback' => $fallback];
        }

        return [...$result, '__fallback' => null];
    }

    public function toArray(): array
    {
        return [
            'ar' => $this->ar,
            'en' => $this->en,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
