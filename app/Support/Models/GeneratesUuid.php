<?php

namespace App\Support\Models;

use Illuminate\Support\Str;

/**
 * Fills the `uuid` column on create.
 *
 * Tables in this project pair a bigint primary key with a NOT NULL `uuid` that
 * is what the API exposes (see the UUID rule in CLAUDE.md). Several models had
 * no generator, so any create() that did not pass one explicitly failed with
 * "Field 'uuid' doesn't have a default value". This trait replaces the boot
 * hook that was previously copy-pasted per model.
 */
trait GeneratesUuid
{
    protected static function bootGeneratesUuid(): void
    {
        static::creating(function ($model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }
}
