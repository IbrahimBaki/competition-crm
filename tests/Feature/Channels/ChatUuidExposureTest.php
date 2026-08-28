<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Organisation\Models\Department;

class ChatUuidExposureTest extends ChatTestCase
{
    public function test_chat_session_endpoints_expose_uuids_only()
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id]);

        $response = $this->postJson('/api/v1/channels/chat/sessions', [
            'department' => $dept->uuid,
        ]);

        $this->assertTrue($response->json('data.session_uuid') !== null);
        $this->assertFalse(ctype_digit((string) $response->json('data.session_uuid')));
    }
}
