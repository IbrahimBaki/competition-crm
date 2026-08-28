<?php

namespace Tests\Unit\Support;

use App\Support\Http\Uploads\AttachmentRules;
use Tests\TestCase;

class AttachmentRulesTest extends TestCase
{
    public function test_file_rules_include_required_validators(): void
    {
        $rules = AttachmentRules::file();

        $this->assertContains('file', $rules);
        $this->assertTrue(count($rules) >= 4);
    }

    public function test_max_size_kb_reads_from_config(): void
    {
        $maxSize = AttachmentRules::maxSizeKb();

        $this->assertEquals(config('security.uploads.max_size_kb'), $maxSize);
    }

    public function test_allowed_mimes_reads_from_config(): void
    {
        $mimes = AttachmentRules::allowedMimes();

        $this->assertIsArray($mimes);
        $this->assertNotEmpty($mimes);
        $this->assertEquals(config('security.uploads.allowed_mimes'), $mimes);
    }

    public function test_allowed_extensions_reads_from_config(): void
    {
        $extensions = AttachmentRules::allowedExtensions();

        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);
        $this->assertEquals(config('security.uploads.allowed_extensions'), $extensions);
    }
}
