<?php

namespace App\Domains\Ai\Models;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiUsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'cost_micros',
        'outcome',
        'ticket_id',
        'user_id',
        'occurred_at',
    ];

    protected $casts = [
        'outcome' => AiUsageOutcome::class,
        'occurred_at' => 'datetime',
    ];

    protected $hidden = ['id'];

    /**
     * Boot the model with UUID generation.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
