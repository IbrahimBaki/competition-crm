<?php

namespace App\Domains\Integrations\Models;

use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ImportRun extends Model
{
    protected $fillable = [
        'kind', 'mode', 'state', 'source_attachment_id',
        'total_rows', 'valid_rows', 'imported_rows', 'failed_rows',
        'error_report', 'created_by_user_id',
    ];
    protected $hidden = ['id'];
    protected $casts = ['error_report' => 'array'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function sourceAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'source_attachment_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRunRow::class);
    }
}
