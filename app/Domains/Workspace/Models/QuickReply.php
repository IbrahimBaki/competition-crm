<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Organisation\Models\Department;
use App\Models\User;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickReply extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'scope',
        'owner_id',
        'department_id',
        'title',
        'body',
        'is_active',
    ];

    protected $casts = [
        'scope' => QuickReplyScope::class,
        'title' => BilingualStringCast::class,
        'body' => BilingualStringCast::class,
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
