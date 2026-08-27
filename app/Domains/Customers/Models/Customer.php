<?php

namespace App\Domains\Customers\Models;

use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = ['uuid', 'name', 'company_account_id', 'preferred_locale'];

    protected $casts = [
        'status' => CustomerStatus::class,
        'blocked_at' => 'datetime',
        'anonymised_at' => 'datetime',
        'merged_at' => 'datetime',
    ];

    /**
     * Boot the model with UUID and name_normalised generation.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
            if (! $model->name_normalised && $model->name) {
                $model->name_normalised = app(TextNormaliser::class)->normaliseName($model->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function companyAccount(): BelongsTo
    {
        return $this->belongsTo(CompanyAccount::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CustomerEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'merged_into_customer_id');
    }

    public function isBlocked(): bool
    {
        return $this->status === CustomerStatus::Blocked;
    }

    public function isMerged(): bool
    {
        return $this->merged_into_customer_id !== null;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $customer = parent::resolveRouteBinding($value, $field);

        if ($customer && $customer->isMerged()) {
            $survivor = $customer->mergedInto;
            $hops = 1;

            while ($survivor && $survivor->isMerged() && $hops < 10) {
                $survivor = $survivor->mergedInto;
                $hops++;
            }

            if ($hops >= 10) {
                throw new ModelNotFoundException;
            }

            return $survivor ?? $customer;
        }

        return $customer;
    }
}
