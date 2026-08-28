<?php

namespace App\Domains\Channels\WebForm\Services\Dedupe;

use App\Domains\Channels\WebForm\Models\WebForm;

final class SubmissionFingerprint
{
    /**
     * @param  array<string, mixed>  $normalisedPayload
     */
    public function for(WebForm $form, array $normalisedPayload, ?string $submitterIdentity): string
    {
        // Sort payload keys to ensure consistent fingerprints regardless of key order
        ksort($normalisedPayload);

        // Normalize string values: trim and lowercase for comparison
        $normalised = [];
        foreach ($normalisedPayload as $key => $value) {
            if (is_string($value)) {
                $normalised[$key] = mb_strtolower(trim($value));
            } else {
                $normalised[$key] = $value;
            }
        }

        // Normalize submitter identity if present
        if ($submitterIdentity) {
            $submitterIdentity = mb_strtolower(trim($submitterIdentity));
        }

        // Hash form key + payload + submitter identity
        $hashInput = json_encode([
            'form_key' => $form->key,
            'payload' => $normalised,
            'submitter_identity' => $submitterIdentity,
        ], JSON_THROW_ON_ERROR);

        return hash('sha256', $hashInput);
    }
}
