<?php

namespace App\Domains\Ticketing\Services\Retention;

use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;
use Illuminate\Support\Facades\DB;

class TicketMessagePurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'ticket_messages';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        $batchSize = config('retention.batch_size', 500);
        $totalRedacted = 0;

        while (true) {
            $redacted = DB::table('ticket_messages')
                ->where('created_at', '<', $policy->cutoff)
                ->whereNull('redacted_at')
                ->limit($batchSize)
                ->update([
                    'body' => '',
                    'redacted_at' => now(),
                ]);

            if ($redacted === 0) {
                break;
            }

            $totalRedacted += $redacted;
        }

        return $totalRedacted;
    }
}
