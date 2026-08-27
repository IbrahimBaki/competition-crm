<?php

namespace Tests\Unit\Channels\Email;

use App\Domains\Channels\Email\Models\InboundClassification;
use App\Domains\Channels\Email\Services\Classification\InboundClassifier;
use App\Domains\Channels\Email\Services\Parsing\ParsedEmail;
use PHPUnit\Framework\TestCase;

class InboundClassifierTest extends TestCase
{
    private InboundClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new InboundClassifier;
    }

    public function test_classifies_bounce_by_content_type(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'mail-server@example.com',
            fromName: null,
            toAddress: null,
            subject: 'Delivery Status Notification',
            textBody: 'Your message could not be delivered',
            htmlBody: null,
            headers: ['content-type' => 'multipart/report; report-type=delivery-status'],
        );

        $result = $this->classifier->classify($email);

        $this->assertEquals(InboundClassification::Bounce, $result);
    }

    public function test_classifies_bounce_by_mailer_daemon(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'MAILER-DAEMON@mail.example.com',
            fromName: null,
            toAddress: null,
            subject: 'Mail Delivery Failed',
            textBody: 'Undeliverable',
            htmlBody: null,
            headers: [],
        );

        $result = $this->classifier->classify($email);

        $this->assertEquals(InboundClassification::Bounce, $result);
    }

    public function test_classifies_auto_reply_by_auto_submitted(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: 'User',
            toAddress: null,
            subject: 'Out of office',
            textBody: 'I am out of the office',
            htmlBody: null,
            headers: ['auto-submitted' => 'auto-replied'],
        );

        $result = $this->classifier->classify($email);

        $this->assertEquals(InboundClassification::AutoReply, $result);
    }

    public function test_classifies_auto_reply_by_precedence(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: 'User',
            toAddress: null,
            subject: 'Auto-reply',
            textBody: 'Thanks for your email',
            htmlBody: null,
            headers: ['precedence' => 'bulk'],
        );

        $result = $this->classifier->classify($email);

        $this->assertEquals(InboundClassification::AutoReply, $result);
    }

    public function test_classifies_human_reply_as_reply(): void
    {
        $email = new ParsedEmail(
            messageId: 'msg@example.com',
            inReplyTo: null,
            referenceIds: [],
            fromAddress: 'user@example.com',
            fromName: 'User',
            toAddress: null,
            subject: 'Re: Your question',
            textBody: 'Here is my answer',
            htmlBody: null,
            headers: [],
        );

        $result = $this->classifier->classify($email);

        $this->assertEquals(InboundClassification::Reply, $result);
    }
}
