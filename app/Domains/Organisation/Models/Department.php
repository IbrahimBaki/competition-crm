<?php

namespace App\Domains\Organisation\Models;

use App\Models\User;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Builder
 */
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'branch_id', 'name', 'code', 'is_active'];

    protected $casts = [
        'name' => 'array',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_department',
            'department_id',
            'user_uuid',
            'id',
            'uuid'
        )->withTimestamps();
    }
}
