<?php

use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Services\TextNormaliser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $normaliser = app(TextNormaliser::class);

        DB::table('customer_contacts')
            ->orderBy('id')
            ->chunk(500, function ($contacts) use ($normaliser) {
                foreach ($contacts as $contact) {
                    try {
                        $type = ContactType::from($contact->type);
                    } catch (ValueError) {
                        continue;
                    }

                    $newNormalised = $normaliser->normaliseContact($contact->value, $type);

                    if ($newNormalised === $contact->value_normalised) {
                        continue;
                    }

                    // Check if the new normalised value would collide with another contact
                    $existing = DB::table('customer_contacts')
                        ->where('type', $contact->type)
                        ->where('value_normalised', $newNormalised)
                        ->where('id', '!=', $contact->id)
                        ->first();

                    if ($existing) {
                        // Collision detected — create a duplicate candidate for manual review
                        $primaryId = min($contact->customer_id, $existing->customer_id);
                        $duplicateId = max($contact->customer_id, $existing->customer_id);

                        // Check if we already have this pair recorded
                        $existing_candidate = DB::table('customer_duplicate_candidates')
                            ->where('customer_id', $primaryId)
                            ->where('duplicate_customer_id', $duplicateId)
                            ->where('rule', 'normalised_phone')
                            ->first();

                        if (! $existing_candidate) {
                            DB::table('customer_duplicate_candidates')->insert([
                                'uuid' => Str::uuid(),
                                'customer_id' => $primaryId,
                                'duplicate_customer_id' => $duplicateId,
                                'status' => 'pending',
                                'rule' => 'normalised_phone',
                                'evidence' => json_encode([
                                    'contact_1_uuid' => $contact->uuid,
                                    'contact_2_uuid' => $existing->uuid,
                                    'normalised_value' => $newNormalised,
                                ]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        continue;
                    }

                    // Safe to update
                    DB::table('customer_contacts')
                        ->where('id', $contact->id)
                        ->update(['value_normalised' => $newNormalised]);
                }
            });
    }

    public function down(): void
    {
        // This migration is not reversible. Pre-normalised values are not recoverable.
        // The migration is idempotent and safe to re-run if it fails partway through.
    }
};
