<?php

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDeliveryAttempt extends Model
{
    protected $fillable = [
        'notification_id',
        'attempt_no',
        'channel',
        'outcome',
        'error_code',
        'error_message',
        'attempted_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
