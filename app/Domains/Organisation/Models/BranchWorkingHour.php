<?php

namespace App\Domains\Organisation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchWorkingHour extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'day_of_week', 'is_working', 'opens_at', 'closes_at'];

    protected $casts = [
        'is_working' => 'boolean',
        'day_of_week' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
