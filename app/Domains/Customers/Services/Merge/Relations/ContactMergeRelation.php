<?php

namespace App\Domains\Customers\Services\Merge\Relations;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Services\Merge\MergeRelation;

final class ContactMergeRelation implements MergeRelation
{
    public function key(): string
    {
        return 'contacts';
    }

    public function moveTo(Customer $survivor, Customer $loser): int
    {
        $loserContacts = CustomerContact::where('customer_id', $loser->id)->get();

        $movedCount = 0;
        $deletedCount = 0;

        foreach ($loserContacts as $loserContact) {
            $existingContact = CustomerContact::where('customer_id', $survivor->id)
                ->where('type', $loserContact->type->value)
                ->where('value_normalised', $loserContact->value_normalised)
                ->first();

            if ($existingContact) {
                // Contact already exists on survivor — delete loser's duplicate
                $loserContact->delete();
                $deletedCount++;
            } else {
                // Move to survivor and clear is_primary (survivor keeps one primary per type)
                $loserContact->update([
                    'customer_id' => $survivor->id,
                    'is_primary' => false,
                ]);
                $movedCount++;
            }
        }

        return $movedCount;
    }
}
