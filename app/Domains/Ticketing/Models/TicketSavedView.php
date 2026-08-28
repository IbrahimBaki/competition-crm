<?php

namespace App\Domains\Ticketing\Models;

use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSavedView extends Model
{
    use GeneratesUuid;
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = ['user_id', 'name', 'query', 'is_shared'];

    protected $casts = [
        'query' => 'array',
        'is_shared' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
