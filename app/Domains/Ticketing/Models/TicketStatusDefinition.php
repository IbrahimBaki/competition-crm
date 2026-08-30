<?php

namespace App\Domains\Ticketing\Models;

use App\Support\I18n\Casts\BilingualStringCast;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatusDefinition extends Model
{
    use GeneratesUuid;
    use HasFactory;

    // The table is `ticket_statuses`; without this Laravel infers
    // `ticket_status_definitions`, which does not exist.
    protected $table = 'ticket_statuses';

    protected $guarded = ['*'];

    protected $fillable = ['key', 'name', 'lifecycle_type', 'is_default', 'position', 'is_active'];

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
