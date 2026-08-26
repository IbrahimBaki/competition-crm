<?php

namespace App\Domains\Customers\Services\Retention;

use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;
use Illuminate\Support\Facades\DB;

class CustomerNotePurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'customer_notes';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        $batchSize = config('retention.batch_size', 500);
        $totalDeleted = 0;

        // Purge notes on anonymised customers first
        while (true) {
            $deleted = DB::table('customer_notes')
                ->whereIn('customer_id', function ($q) {
                    $q->select('id')->from('customers')->where('anonymised_at', '!=', null);
                })
                ->limit($batchSize)
                ->delete();

            if ($deleted === 0) {
                break;
            }

            $totalDeleted += $deleted;
        }

        // Then purge old notes
        while (true) {
            $deleted = DB::table('customer_notes')
                ->where('created_at', '<', $policy->cutoff)
                ->limit($batchSize)
                ->delete();

            if ($deleted === 0) {
                break;
            }

            $totalDeleted += $deleted;
        }

        return $totalDeleted;
    }
}
