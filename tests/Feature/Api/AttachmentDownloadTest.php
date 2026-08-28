<?php

namespace Tests\Feature\Api;

use App\Domains\Security\Models\Role;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\ScanState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttachmentDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $uploader;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('attachments');

        $adminRole = Role::factory()->create(['name' => Role::ADMINISTRATOR]);

        $this->uploader = User::factory()->create();
        $this->uploader->roles()->attach($adminRole);

        $this->otherUser = User::factory()->create();
    }

    protected function createAttachment(ScanState $state = ScanState::Clean, ?User $uploader = null): Attachment
    {
        $uploader ??= $this->uploader;

        Storage::disk('attachments')->put('test/file.txt', 'test content');

        return Attachment::create([
            'uuid' => Str::uuid()->toString(),
            'disk' => 'attachments',
            'storage_key' => 'test/file.txt',
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain',
            'size_bytes' => 12,
            'scan_state' => $state,
            'uploaded_by' => $uploader->id,
        ]);
    }

    public function test_download_pending_attachment_returns_409(): void
    {
        $attachment = $this->createAttachment(ScanState::Pending);

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertConflict();
        $response->assertJsonPath('error.code', 'attachment.scan_pending');
    }

    public function test_download_infected_attachment_returns_422(): void
    {
        $attachment = $this->createAttachment(ScanState::Infected);

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'attachment.scan_failed');
    }

    public function test_download_failed_scan_returns_422(): void
    {
        $attachment = $this->createAttachment(ScanState::Failed);

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'attachment.scan_failed');
    }

    public function test_download_clean_attachment_returns_file(): void
    {
        $attachment = $this->createAttachment(ScanState::Clean);

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertOk();
    }

    public function test_uploader_can_download_own_attachment(): void
    {
        $attachment = $this->createAttachment(ScanState::Clean, $this->uploader);

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertOk();
    }

    public function test_unauthorized_user_with_valid_uuid_returns_404(): void
    {
        $attachment = $this->createAttachment(ScanState::Clean, $this->uploader);

        $response = $this->actingAs($this->otherUser)
            ->get("/api/v1/attachments/{$attachment->uuid}");

        // Critical: must be 404 not_found, never 403 unauthorized
        $response->assertNotFound();
        $response->assertJsonPath('error.code', 'not_found');
    }

    public function test_unauthenticated_user_cannot_download(): void
    {
        $attachment = $this->createAttachment(ScanState::Clean);

        $response = $this->get("/api/v1/attachments/{$attachment->uuid}");

        $response->assertUnauthorized();
    }

    public function test_nonexistent_attachment_returns_404(): void
    {
        $fakeUuid = Str::uuid()->toString();

        $response = $this->actingAs($this->uploader)
            ->get("/api/v1/attachments/{$fakeUuid}");

        $response->assertNotFound();
    }
}
