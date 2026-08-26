<?php

namespace App\Domains\Organisation\Models;

use App\Support\I18n\BilingualString;
use Database\Factories\BranchHolidayFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Builder
 *
 * @property BilingualString $name
 */
class BranchHoliday extends Model
{
    /** @use HasFactory<BranchHolidayFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'branch_id', 'name', 'date', 'recurring_month_day'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'date' => 'date',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
