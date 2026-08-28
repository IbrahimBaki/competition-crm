<?php

namespace App\Domains\Channels\WebForm\Models;

use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebFormField extends Model
{
    use HasFactory;

    protected $fillable = ['web_form_id', 'key', 'type', 'is_required', 'label', 'options', 'validation', 'maps_to', 'position'];

    protected $casts = [
        'label' => BilingualStringCast::class,
        'options' => 'array',
        'validation' => 'array',
        'is_required' => 'boolean',
        'type' => WebFormFieldType::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(WebForm::class, 'web_form_id');
    }
}
