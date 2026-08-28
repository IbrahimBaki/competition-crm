<?php

namespace Tests\Unit\Customers;

use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Services\ArabicTextNormaliser;
use Tests\TestCase;

class PhoneNormalisationTest extends TestCase
{
    private ArabicTextNormaliser $normaliser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normaliser = app(ArabicTextNormaliser::class);
    }

    public function test_phone_with_leading_zero_normalises_to_e164(): void
    {
        $result = $this->normaliser->normaliseContact('0501234567', ContactType::Phone);
        $this->assertEquals('+966501234567', $result);
    }

    public function test_phone_with_country_code_normalises_to_e164(): void
    {
        $result = $this->normaliser->normaliseContact('+966501234567', ContactType::Phone);
        $this->assertEquals('+966501234567', $result);
    }

    public function test_phone_with_leading_00_normalises_to_e164(): void
    {
        $result = $this->normaliser->normaliseContact('00966501234567', ContactType::Phone);
        $this->assertEquals('+966501234567', $result);
    }

    public function test_phone_with_spaces_normalises_to_e164(): void
    {
        $result = $this->normaliser->normaliseContact('+966 50 123 4567', ContactType::Phone);
        $this->assertEquals('+966501234567', $result);
    }

    public function test_phone_with_arabic_indic_digits_normalises_to_e164(): void
    {
        $result = $this->normaliser->normaliseContact('٠٥٠١٢٣٤٥٦٧', ContactType::Phone);
        $this->assertEquals('+966501234567', $result);
    }

    public function test_whatsapp_normalises_same_as_phone(): void
    {
        $phoneResult = $this->normaliser->normaliseContact('0501234567', ContactType::Phone);
        $whatsappResult = $this->normaliser->normaliseContact('0501234567', ContactType::Whatsapp);
        $this->assertEquals($phoneResult, $whatsappResult);
    }

    public function test_sms_normalises_same_as_phone(): void
    {
        $phoneResult = $this->normaliser->normaliseContact('0501234567', ContactType::Phone);
        $smsResult = $this->normaliser->normaliseContact('0501234567', ContactType::Sms);
        $this->assertEquals($phoneResult, $smsResult);
    }

    public function test_email_normalises_to_lowercase(): void
    {
        $result = $this->normaliser->normaliseContact('Test@Example.COM', ContactType::Email);
        $this->assertEquals('test@example.com', $result);
    }

    public function test_portal_login_normalises_to_lowercase(): void
    {
        $result = $this->normaliser->normaliseContact('User@DOMAIN', ContactType::PortalLogin);
        $this->assertEquals('user@domain', $result);
    }

    public function test_empty_phone_returns_empty_string(): void
    {
        $result = $this->normaliser->normaliseContact('', ContactType::Phone);
        $this->assertEquals('', $result);
    }

    public function test_phone_with_only_spaces_returns_empty_string(): void
    {
        $result = $this->normaliser->normaliseContact('   ', ContactType::Phone);
        $this->assertEquals('', $result);
    }
}
