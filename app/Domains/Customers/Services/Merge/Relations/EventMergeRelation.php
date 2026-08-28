<?php

namespace App\Domains\Customers\Services\Merge\Relations;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEvent;
use App\Domains\Customers\Services\Merge\MergeRelation;

final class EventMergeRelation implements MergeRelation
{
    public function key(): string
    {
        return 'events';
    }

    public function moveTo(Customer $survivor, Customer $loser): int
    {
        return CustomerEvent::where('customer_id', $loser->id)
            ->update(['customer_id' => $survivor->id]);
    }
}
