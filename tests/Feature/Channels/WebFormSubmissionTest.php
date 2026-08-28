<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormField;
use App\Domains\Channels\WebForm\Models\WebFormFieldType;
use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WebFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();
    }

    public function test_submit_web_form_creates_ticket(): void
    {
        $form = $this->createWebForm();

        $response = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", [
            'answers' => [
                'name_field' => 'John Doe',
                'email_field' => 'john@example.com',
                'message_field' => 'Help me please',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'ticket_reference',
                'tracking_token',
                'was_duplicate',
                'status_url',
            ],
        ]);

        $this->assertDatabaseHas('web_form_submissions', [
            'web_form_id' => $form->id,
            'state' => 'accepted',
        ]);

        $ticket = $form->submissions()->latest()->first()->ticket;
        $this->assertNotNull($ticket);
        $this->assertEquals($form->department_id, $ticket->department_id);
    }

    public function test_duplicate_submission_returns_original_ticket(): void
    {
        $form = $this->createWebForm();
        $payload = [
            'answers' => [
                'name_field' => 'Jane Doe',
                'email_field' => 'jane@example.com',
                'message_field' => 'Same message',
            ],
        ];

        $response1 = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", $payload);
        $reference1 = $response1->json('data.ticket_reference');
        $token1 = $response1->json('data.tracking_token');

        $response2 = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", $payload);
        $reference2 = $response2->json('data.ticket_reference');
        $token2 = $response2->json('data.tracking_token');

        $this->assertEquals($reference1, $reference2);
        $this->assertEquals($token1, $token2);
        $this->assertTrue($response2->json('data.was_duplicate'));

        $this->assertDatabaseHas('web_form_submissions', [
            'state' => 'duplicate',
            'duplicate_of_id' => WebFormSubmission::where('state', 'accepted')->first()->id,
        ]);
    }

    public function test_unknown_field_is_rejected(): void
    {
        $form = $this->createWebForm();

        $response = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", [
            'answers' => [
                'name_field' => 'John',
                'email_field' => 'john@example.com',
                'message_field' => 'Hello',
                'unknown_field' => 'This should be rejected',
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('meta.fields.unknown_field.0', fn ($val) => str_contains($val, 'validation'));
    }

    public function test_required_field_missing_is_rejected(): void
    {
        $form = $this->createWebForm();

        $response = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", [
            'answers' => [
                'email_field' => 'john@example.com',
                'message_field' => 'Hello',
            ],
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('name_field', $response->json('meta.fields'));
    }

    public function test_inactive_form_returns_404(): void
    {
        $form = $this->createWebForm(['is_active' => false]);

        $response = $this->postJson("/api/v1/channels/web-forms/{$form->key}/submissions", [
            'answers' => ['name_field' => 'John'],
        ]);

        $response->assertStatus(404);
    }

    private function createWebForm(array $attributes = []): WebForm
    {
        $form = WebForm::factory()->create(array_merge([
            'is_active' => true,
        ], $attributes));

        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'name_field',
            'type' => WebFormFieldType::Text,
            'is_required' => true,
            'maps_to' => 'name',
            'position' => 1,
        ]);

        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'email_field',
            'type' => WebFormFieldType::Email,
            'is_required' => true,
            'maps_to' => 'email',
            'position' => 2,
        ]);

        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'message_field',
            'type' => WebFormFieldType::TextArea,
            'is_required' => true,
            'maps_to' => 'body',
            'position' => 3,
        ]);

        return $form;
    }
}
