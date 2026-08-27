<?php

namespace App\Domains\Channels\Messaging\Models;

use App\Domains\Ticketing\Models\MessageChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProviderMessageTemplate extends Model
{
    protected $fillable = [
        'uuid',
        'channel',
        'key',
        'provider_template_name',
        'body_en',
        'body_ar',
        'variables',
        'is_active',
        'approved_at',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'variables' => 'array',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
