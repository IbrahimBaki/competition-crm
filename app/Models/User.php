<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $uuid
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'uuid' => 'string',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'failed_login_count' => 'integer',
            'locked_until' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Boot the model with UUID generation.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branch', 'user_uuid', 'branch_id', 'uuid', 'id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryBranch(): BelongsToMany
    {
        return $this->branches()->wherePivot('is_primary', true);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_department', 'user_uuid', 'department_id', 'uuid', 'id')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('App\Domains\Security\Models\Role', 'user_role', 'user_uuid', 'role_id', 'uuid', 'id')
            ->withTimestamps();
    }

    public function permissionKeys(): array
    {
        if (! isset($this->_permissionKeysCache)) {
            $this->_permissionKeysCache = $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn ($role) => $role->permissions->pluck('permission_key'))
                ->unique()
                ->values()
                ->toArray();
        }

        return $this->_permissionKeysCache;
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    private ?array $_permissionKeysCache = null;
}
