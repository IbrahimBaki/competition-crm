<?php

namespace App\Domains\Sla\Models;

use App\Domains\Organisation\Models\Branch;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['uuid', 'branch_id', 'name', 'is_default', 'is_active', 'warning_threshold_percent'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SlaTarget::class);
    }

    public function clocks(): HasMany
    {
        return $this->hasMany(TicketSlaClock::class);
    }
}
