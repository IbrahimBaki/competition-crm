<?php

namespace App\Domains\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $fillable = [
        'uuid',
        'event_id',
        'event_type',
        'recipient_user_id',
        'channel',
        'state',
        'locale',
        'payload',
        'subject',
        'body',
        'attempts',
        'failure_code',
        'failure_reason',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
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

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(NotificationDeliveryAttempt::class);
    }
}
