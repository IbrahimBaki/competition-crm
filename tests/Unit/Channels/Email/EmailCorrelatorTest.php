<?php

namespace Tests\Unit\Channels\Email;

use App\Domains\Channels\Email\Services\Correlation\EmailCorrelator;
use App\Domains\Channels\Email\Services\Parsing\ParsedEmail;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCorrelatorTest extends TestCase
{
    use RefreshDatabase;

    private EmailCorrelator $correlator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->correlator = app(EmailCorrelator::class);
    }

    public function test_correlate_by_in_reply_to(): void
    {
        $ticket = Ticket::factory()->create();
        $message = TicketMessage::factory()->for($ticket)->create([
            'channel' => MessageChannel::Email->value,
            'external_message_id' => 'msg-123@example.com',
        ]);

        $email = new ParsedEmail(
            messageId: 'msg-456@example.com',
            inReplyTo: 'msg-123@example.com',
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: null,
            toAddress: null,
            subject: 'Re: Test',
            textBody: 'Reply text',
            htmlBody: null,
            headers: [],
        );

        $result = $this->correlator->correlate($email);

        $this->assertNotNull($result);
        $this->assertEquals($ticket->id, $result->id);
    }

    public function test_correlate_by_references(): void
    {
        $ticket = Ticket::factory()->create();
        TicketMessage::factory()->for($ticket)->create([
            'channel' => MessageChannel::Email->value,
            'external_message_id' => 'msg-old@example.com',
        ]);

        $email = new ParsedEmail(
            messageId: 'msg-new@example.com',
            inReplyTo: null,
            referenceIds: ['msg-very-old@example.com', 'msg-old@example.com'],
            fromAddress: 'user@example.com',
            fromName: null,
            toAddress: null,
            subject: 'Re: Test',
            textBody: 'Reply text',
            htmlBody: null,
            headers: [],
        );

        $result = $this->correlator->correlate($email);

        $this->assertNotNull($result);
        $this->assertEquals($ticket->id, $result->id);
    }

    public function test_correlate_by_ticket_reference_token(): void
    {
        $ticket = Ticket::factory()->create(['reference' => 'TKT-202608-000123']);

        $email = new ParsedEmail(
            messageId: 'msg-new@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: null,
            toAddress: null,
            subject: 'Re: Issue with order TKT-202608-000123',
            textBody: 'Reply text',
            htmlBody: null,
            headers: [],
        );

        $result = $this->correlator->correlate($email);

        $this->assertNotNull($result);
        $this->assertEquals($ticket->id, $result->id);
    }

    public function test_correlate_by_reference_in_body(): void
    {
        $ticket = Ticket::factory()->create(['reference' => 'TKT-202608-000456']);

        $email = new ParsedEmail(
            messageId: 'msg-new@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: null,
            toAddress: null,
            subject: 'Quick question',
            textBody: 'About ticket TKT-202608-000456, I have a question...',
            htmlBody: null,
            headers: [],
        );

        $result = $this->correlator->correlate($email);

        $this->assertNotNull($result);
        $this->assertEquals($ticket->id, $result->id);
    }

    public function test_correlate_returns_null_when_no_match(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg-new@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: null,
            toAddress: null,
            subject: 'New inquiry',
            textBody: 'This is a new ticket',
            htmlBody: null,
            headers: [],
        );

        $result = $this->correlator->correlate($email);

        $this->assertNull($result);
    }
}
