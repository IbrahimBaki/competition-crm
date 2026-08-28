<?php

namespace App\Domains\Integrations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ImportRunRow extends Model
{
    protected $fillable = [
        'import_run_id', 'row_number', 'state', 'errors',
        'external_ref', 'created_entity_uuid',
    ];

    protected $hidden = ['id'];

    protected $casts = ['errors' => 'array'];

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class);
    }
}
