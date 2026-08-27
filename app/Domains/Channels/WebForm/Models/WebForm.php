<?php

namespace App\Domains\Channels\WebForm\Models;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebForm extends Model
{
    protected $fillable = ['key', 'title', 'description', 'department_id', 'ticket_category_id', 'default_priority', 'is_active', 'acknowledgement_template_key'];

    protected $casts = [
        'title' => BilingualStringCast::class,
        'description' => BilingualStringCast::class,
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function fields(): HasMany
    {
        return $this->hasMany(WebFormField::class)->orderBy('position');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }
}
