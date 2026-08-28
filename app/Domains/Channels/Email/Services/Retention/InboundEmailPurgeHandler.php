<?php

namespace App\Domains\Channels\Email\Services\Retention;

use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InboundEmailPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'inbound_email_messages';
    }

    public function purge(RetentionPolicy $policy): int
    {
        $retentionDays = config('channels.email.raw_retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        $count = 0;

        DB::transaction(function () use ($cutoffDate, &$count) {
            $rows = InboundEmailMessage::where('created_at', '<', $cutoffDate)
                ->where(fn ($q) => $q->where('state', InboundState::Processed->value)
                    ->orWhere('state', InboundState::Suppressed->value))
                ->limit(1000)
                ->get();

            foreach ($rows as $row) {
                if ($row->raw_path) {
                    Storage::disk('local')->delete($row->raw_path);
                }
                $row->delete();
                $count++;
            }
        });

        return $count;
    }
}
