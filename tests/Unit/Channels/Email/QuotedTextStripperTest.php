<?php

namespace Tests\Unit\Channels\Email;

use App\Domains\Channels\Email\Services\Parsing\QuotedTextStripper;
use PHPUnit\Framework\TestCase;

class QuotedTextStripperTest extends TestCase
{
    private QuotedTextStripper $stripper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripper = new QuotedTextStripper;
    }

    public function test_removes_quoted_lines(): void
    {
        $body = "My reply\n\n> Original quoted text\n> More quoted text";
        $result = $this->stripper->strip($body);

        $this->assertEquals('My reply', $result);
    }

    public function test_removes_signature(): void
    {
        $body = "My message\n-- \nJohn Doe";
        $result = $this->stripper->strip($body);

        $this->assertEquals('My message', $result);
    }

    public function test_removes_on_wrote_header(): void
    {
        $body = "My reply\n\nOn 2026-01-01, John Doe wrote:\n> Original message";
        $result = $this->stripper->strip($body);

        $this->assertStringContainsString('My reply', $result);
        $this->assertStringNotContainsString('On', $result);
    }

    public function test_preserves_arabic_body(): void
    {
        $body = "ردي على الرسالة\n\n> النص الأصلي";
        $result = $this->stripper->strip($body);

        $this->assertStringContainsString('ردي', $result);
    }

    public function test_all_quoted_returns_original(): void
    {
        $body = "> All quoted\n> No original content";
        $result = $this->stripper->strip($body);

        $this->assertEquals($body, $result);
    }

    public function test_empty_input_returns_empty(): void
    {
        $result = $this->stripper->strip('');
        $this->assertEquals('', $result);
    }
}
