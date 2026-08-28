<?php

namespace App\Domains\Customers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyAccount extends Model
{
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = ['name', 'service_tier'];

    protected $casts = [];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
