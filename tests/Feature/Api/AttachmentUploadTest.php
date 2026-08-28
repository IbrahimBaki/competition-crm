<?php

namespace Tests\Feature\Api;

use App\Domains\Security\Models\Role;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\ScanState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('attachments');

        $this->user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => Role::ADMINISTRATOR]);
        $this->user->roles()->attach($adminRole);
    }

    public function test_authenticated_user_can_upload_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 512);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertCreated();
        $response->assertJsonStructure(['data' => ['uuid', 'original_name', 'mime_type', 'size_bytes', 'scan_state', 'created_at']]);
        $this->assertEquals('pending', $response->json('data.scan_state'));
    }

    public function test_upload_response_does_not_expose_storage_key(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertCreated();
        $this->assertArrayNotHasKey('storage_key', $response->json('data'));
        $this->assertArrayNotHasKey('disk', $response->json('data'));
        $this->assertArrayNotHasKey('checksum_sha256', $response->json('data'));
    }

    public function test_file_too_large_returns_validation_error(): void
    {
        config(['security.uploads.max_size_kb' => 100]);
        $file = UploadedFile::fake()->create('large.pdf', 512 * 1024); // 512 KB

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_disallowed_file_type_returns_validation_error(): void
    {
        config(['security.uploads.allowed_extensions' => ['pdf', 'txt']]);
        $file = UploadedFile::fake()->create('program.exe', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertUnprocessable();
    }

    public function test_missing_file_returns_validation_error(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', []);

        $response->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_upload(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertUnauthorized();
    }

    public function test_attachment_stored_on_private_disk(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertCreated();
        $uuid = $response->json('data.uuid');

        $attachment = Attachment::where('uuid', $uuid)->first();
        $this->assertEquals('attachments', $attachment->disk);
        $this->assertStringNotContainsString('public/', $attachment->storage_key);
        $this->assertTrue(str_contains($attachment->storage_key, '/'));
    }

    public function test_attachment_original_name_preserved_as_metadata(): void
    {
        $originalName = 'my-important-document.pdf';
        $file = UploadedFile::fake()->create($originalName, 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $response->assertCreated();
        $this->assertEquals($originalName, $response->json('data.original_name'));

        $uuid = $response->json('data.uuid');
        $attachment = Attachment::where('uuid', $uuid)->first();
        $this->assertEquals($originalName, $attachment->original_name);
    }

    public function test_attachment_starts_in_pending_state(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/attachments', ['file' => $file]);

        $uuid = $response->json('data.uuid');
        $attachment = Attachment::where('uuid', $uuid)->first();

        $this->assertEquals(ScanState::Pending, $attachment->scan_state);
    }
}
