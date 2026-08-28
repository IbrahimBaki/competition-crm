<?php

namespace App\Domains\Organisation\Models;

use App\Models\User;
use App\Support\I18n\BilingualString;
use App\Support\I18n\Casts\BilingualStringCast;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Builder
 *
 * @property BilingualString $name
 */
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'name', 'code', 'timezone', 'is_24_7', 'is_active'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'is_24_7' => 'boolean',
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

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_branch',
            'branch_id',
            'user_uuid',
            'id',
            'uuid'
        )->withPivot('is_primary')->withTimestamps();
    }

    public function workingHours()
    {
        return $this->hasMany(BranchWorkingHour::class);
    }

    public function holidays()
    {
        return $this->hasMany(BranchHoliday::class);
    }

    public function timezoneObject(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone ?: 'UTC');
    }
}
