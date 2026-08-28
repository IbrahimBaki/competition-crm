<?php

namespace App\Domains\Customers\Services\Merge\Relations;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\Merge\MergeRelation;
use App\Support\Attachments\Attachment;

final class AttachmentMergeRelation implements MergeRelation
{
    public function key(): string
    {
        return 'attachments';
    }

    public function moveTo(Customer $survivor, Customer $loser): int
    {
        return Attachment::where('attachable_type', Customer::class)
            ->where('attachable_id', $loser->id)
            ->update(['attachable_id' => $survivor->id]);
    }
}
