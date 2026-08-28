<?php

namespace Tests\Unit\Support;

use App\Support\Logging\RedactSensitiveProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use Tests\TestCase;

class LogRedactionTest extends TestCase
{
    private RedactSensitiveProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new RedactSensitiveProcessor;
    }

    public function test_redacts_password_from_context(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: ['password' => 'secret123'],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted]', $result->context['password']);
    }

    public function test_redacts_token_from_context(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: ['token' => 'secret-token'],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted]', $result->context['token']);
    }

    public function test_redacts_recovery_code(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: ['recovery_code' => 'code-123'],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted]', $result->context['recovery_code']);
    }

    public function test_redacts_otp(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: ['otp' => '123456'],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted]', $result->context['otp']);
    }

    public function test_redacts_pii_bundle_email_and_phone(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: [
                'user_data' => [
                    'email' => 'user@example.com',
                    'phone' => '+1234567890',
                ],
            ],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted:pii]', $result->context['user_data']);
    }

    public function test_redacts_pii_bundle_email_and_national_id(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: [
                'user_data' => [
                    'email' => 'user@example.com',
                    'national_id' => '123456789',
                ],
            ],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted:pii]', $result->context['user_data']);
    }

    public function test_does_not_redact_email_alone(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: [
                'email' => 'user@example.com',
            ],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('user@example.com', $result->context['email']);
    }

    public function test_redacts_case_insensitively(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Info,
            message: 'test',
            context: [
                'PASSWORD' => 'secret123',
                'Token' => 'secret-token',
            ],
        );

        $result = ($this->processor)($record);

        $this->assertEquals('[redacted]', $result->context['PASSWORD']);
        $this->assertEquals('[redacted]', $result->context['Token']);
    }
}
