<?php

namespace App\Domains\Channels\Email\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLoopGuard extends Model
{
    protected $table = 'email_loop_guards';

    protected $fillable = [
        'sender_hash',
        'window_started_at',
        'hits',
        'tripped',
    ];

    protected $casts = [
        'window_started_at' => 'datetime',
        'tripped' => 'boolean',
    ];
}
