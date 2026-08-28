<?php

namespace Tests\Unit\Security;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class StaffPasswordRulesTest extends TestCase
{
    public function test_rejects_password_less_than_12_characters(): void
    {
        $validator = Validator::make(
            ['password' => 'Short123!'],
            ['password' => 'required|min:12|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/']
        );

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_password_without_uppercase(): void
    {
        $validator = Validator::make(
            ['password' => 'password123!'],
            ['password' => 'required|min:12|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/']
        );

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_password_without_digit(): void
    {
        $validator = Validator::make(
            ['password' => 'PasswordSymbol!'],
            ['password' => 'required|min:12|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/']
        );

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_password_without_symbol(): void
    {
        $validator = Validator::make(
            ['password' => 'Password123'],
            ['password' => 'required|min:12|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/']
        );

        $this->assertTrue($validator->fails());
    }

    public function test_accepts_strong_password(): void
    {
        $validator = Validator::make(
            ['password' => 'StrongPass123!'],
            ['password' => 'required|min:12|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/']
        );

        $this->assertTrue($validator->passes());
    }
}
