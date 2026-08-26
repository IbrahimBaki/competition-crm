<?php

namespace App\Domains\Customers\Services\Merge;

use App\Domains\Customers\Models\Customer;

interface MergeRelation
{
    public function key(): string;

    public function moveTo(Customer $survivor, Customer $loser): int;
}
