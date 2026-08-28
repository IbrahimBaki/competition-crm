<?php

namespace App\Support\Attachments;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $disk
 * @property string $storage_key
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property string|null $checksum_sha256
 * @property ScanState $scan_state
 * @property string|null $scan_reason
 * @property Carbon|null $scanned_at
 * @property string|null $attachable_type
 * @property int|null $attachable_id
 * @property int $uploaded_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Attachment extends Model
{
    protected $guarded = ['*'];

    protected $hidden = ['disk', 'storage_key', 'checksum_sha256'];

    protected $casts = [
        'scan_state' => ScanState::class,
        'scanned_at' => 'datetime',
    ];

    protected $fillable = [];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<User, Attachment>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return MorphTo<Model>
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
