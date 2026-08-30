<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\ScanState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        // Attachment is fully guarded; Factory::make() runs inside
        // Model::unguarded(), so these still apply.
        return [
            'uuid' => (string) Str::uuid(),
            'disk' => config('security.uploads.disk', 'local'),
            'storage_key' => 'attachments/'.Str::uuid().'.pdf',
            'original_name' => $this->faker->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $this->faker->numberBetween(1024, 1024 * 1024),
            'checksum_sha256' => hash('sha256', (string) Str::uuid()),
            'scan_state' => ScanState::Clean,
            'scan_reason' => null,
            'scanned_at' => now(),
            'uploaded_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'scan_state' => ScanState::Pending,
            'scanned_at' => null,
        ]);
    }

    public function infected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'scan_state' => ScanState::Infected,
            'scan_reason' => 'malware.signature_match',
            'scanned_at' => now(),
        ]);
    }
}
