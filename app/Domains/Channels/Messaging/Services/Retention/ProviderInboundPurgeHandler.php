<?php

namespace App\Domains\Channels\Messaging\Services\Retention;

use App\Domains\Channels\Messaging\Models\ProviderInboundMessage;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;
use Illuminate\Support\Facades\DB;

final class ProviderInboundPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'provider_inbound_messages';
    }

    public function purge(RetentionPolicy $policy): int
    {
        $retentionDays = config('channels.whatsapp.raw_retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        $count = 0;

        DB::transaction(function () use ($cutoffDate, &$count) {
            $rows = ProviderInboundMessage::where('received_at', '<', $cutoffDate)
                ->where('state', 'processed')
                ->limit(1000)
                ->get();

            foreach ($rows as $row) {
                $row->update(['raw_payload' => null]);
                $count++;
            }
        });

        return $count;
    }
}
