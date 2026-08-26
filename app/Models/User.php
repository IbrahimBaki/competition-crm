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

/**
 * @property string $uuid
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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

    /**
     * TODO(BE-03): Replace with real permission resolver.
     * For now, hardcode admin@example.com as having all permissions.
     */
    public function hasPermission(string $key): bool
    {
        return $this->email === 'admin@example.com';
    }

    private ?array $_permissionKeysCache = null;
}
