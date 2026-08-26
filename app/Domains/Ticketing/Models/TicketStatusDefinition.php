<?php

namespace App\Domains\Ticketing\Models;

use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;

class TicketStatusDefinition extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['name', 'lifecycle_type', 'is_default', 'position', 'is_active'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'lifecycle_type' => TicketStatus::class,
        'is_default' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function lifecycleType(): TicketStatus
    {
        return $this->lifecycle_type;
    }

    public function stopsSlaClock(): bool
    {
        return $this->lifecycle_type->stopsSlaClock();
    }
}
