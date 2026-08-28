<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\ScanState;
use Illuminate\Http\UploadedFile;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketMessageAttachmentTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_attachment_linked_to_message(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'attachments.upload');
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        // Upload file
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $uploadResponse = $this->actingAs($agent)->postJson('/api/v1/attachments', [
            'file' => $file,
        ]);

        $attachmentUuid = $uploadResponse->json('data.uuid');

        // Create message with attachment
        $messageResponse = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Document attached',
                'channel' => MessageChannel::Email->value,
                'attachment_uuids' => [$attachmentUuid],
            ]
        );

        $messageResponse->assertCreated();
        $attachments = $messageResponse->json('data.attachments');
        $this->assertCount(1, $attachments);
        $this->assertEquals($attachmentUuid, $attachments[0]['uuid']);
    }

    public function test_pending_scan_attachment_rejected(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        // Create a pending-scan attachment
        $attachment = Attachment::factory()->create([
            'uploaded_by' => $agent->id,
            'scan_state' => ScanState::Pending,
        ]);

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'With attachment',
                'channel' => MessageChannel::Email->value,
                'attachment_uuids' => [$attachment->uuid],
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'attachment.scan_pending');

        // Message should NOT be created
        $this->assertEquals(0, $ticket->messages()->count());
    }

    public function test_infected_attachment_rejected(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        // Create an infected attachment
        $attachment = Attachment::factory()->create([
            'uploaded_by' => $agent->id,
            'scan_state' => ScanState::Infected,
        ]);

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'With attachment',
                'channel' => MessageChannel::Email->value,
                'attachment_uuids' => [$attachment->uuid],
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'attachment.scan_failed');

        // Message should NOT be created
        $this->assertEquals(0, $ticket->messages()->count());
    }

    public function test_attachment_uploaded_by_different_user_rejected(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        // Create attachment uploaded by different user
        $attachment = Attachment::factory()->create([
            'uploaded_by' => $otherUser->id,
            'scan_state' => ScanState::Clean,
        ]);

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'With attachment',
                'channel' => MessageChannel::Email->value,
                'attachment_uuids' => [$attachment->uuid],
            ]
        );

        $response->assertStatus(403);
    }
}
