<?php

namespace App\Domains\Notifications\Models;

use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'uuid',
        'code',
        'channel',
        'subject',
        'body',
        'placeholders',
        'is_active',
    ];

    protected $casts = [
        'subject' => BilingualStringCast::class,
        'body' => BilingualStringCast::class,
        'placeholders' => 'json',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! $model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }
}
