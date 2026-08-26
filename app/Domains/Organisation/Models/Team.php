<?php

namespace App\Domains\Organisation\Models;

use App\Support\I18n\BilingualString;
use App\Support\I18n\Casts\BilingualStringCast;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Builder
 *
 * @property BilingualString $name
 */
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'department_id', 'name', 'code', 'is_active'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
