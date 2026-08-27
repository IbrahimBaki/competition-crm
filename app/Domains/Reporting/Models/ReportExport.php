<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    protected $table = 'report_exports';

    protected $fillable = [
        'uuid',
        'report_key',
        'format',
        'filters',
        'requested_by_user_id',
        'state',
        'row_count',
        'attachment_id',
        'failure_code',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'row_count' => 'integer',
        'expires_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}
