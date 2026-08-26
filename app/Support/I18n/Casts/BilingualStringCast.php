<?php

namespace App\Support\I18n\Casts;

use App\Support\I18n\BilingualString;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class BilingualStringCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): BilingualString
    {
        if ($value === null) {
            return new BilingualString(null, null);
        }

        $decoded = json_decode($value, associative: true);
        if ($decoded === null || ! is_array($decoded)) {
            return new BilingualString(null, null);
        }

        return BilingualString::fromArray($decoded);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof BilingualString) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if (is_array($value)) {
            return json_encode(BilingualString::fromArray($value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        throw new InvalidArgumentException(__('errors.bilingual.invalid_payload'));
    }
}
