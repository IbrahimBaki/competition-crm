<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Ticketing\Models\TicketReference;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Allocates unique ticket references in the format TKT-YYYYMM-NNNNNN.
 *
 * References are never reused: the counter persists even when tickets are deleted.
 * On SQLite (dev/test), the transaction provides correctness without FOR UPDATE.
 */
class TicketReferenceGenerator
{
    public function next(?CarbonInterface $at = null): string
    {
        $at ??= now();
        $period = $at->format('Y-m');

        return DB::transaction(function () use ($period) {
            $reference = TicketReference::lockForUpdate()
                ->firstOrCreate(
                    ['period' => $period],
                    ['last_sequence' => 0]
                );

            $reference->increment('last_sequence');
            $sequence = str_pad((string) $reference->last_sequence, 6, '0', STR_PAD_LEFT);

            return "TKT-{$reference->period}-{$sequence}";
        });
    }
}
