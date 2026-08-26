<?php

namespace App\Domains\Customers\Services\Merge\Relations;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Services\Merge\MergeRelation;

final class NoteMergeRelation implements MergeRelation
{
    public function key(): string
    {
        return 'notes';
    }

    public function moveTo(Customer $survivor, Customer $loser): int
    {
        return CustomerNote::where('customer_id', $loser->id)
            ->update(['customer_id' => $survivor->id]);
    }
}
