<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Permissions\PermissionKey;
use PHPUnit\Framework\TestCase;

class PermissionKeyTest extends TestCase
{
    public function test_all_permission_keys_match_format_regex(): void
    {
        $keys = PermissionKey::all();
        $regex = '/^[a-z]+(\.[a-z_]+){1,2}(\.(own|team|department|any))?$/';

        foreach ($keys as $key) {
            $this->assertMatchesRegularExpression(
                $regex,
                $key,
                "Permission key '{$key}' does not match expected format"
            );
        }
    }

    public function test_all_constant_are_in_all_method(): void
    {
        $reflection = new \ReflectionClass(PermissionKey::class);
        $constants = $reflection->getConstants();
        $allKeys = PermissionKey::all();

        $declaredKeys = array_filter($constants, fn ($value) => is_string($value));

        foreach ($declaredKeys as $key => $value) {
            $this->assertContains(
                $value,
                $allKeys,
                "Constant PermissionKey::{$key} is not in PermissionKey::all()"
            );
        }
    }

    public function test_parse_returns_correct_structure(): void
    {
        $parsed = PermissionKey::parse('tickets.view.department');

        $this->assertEquals('tickets', $parsed['module']);
        $this->assertEquals('view', $parsed['action']);
        $this->assertEquals('department', $parsed['scope']);
    }

    public function test_parse_handles_keys_without_scope(): void
    {
        $parsed = PermissionKey::parse('admin.roles.manage');

        $this->assertEquals('admin', $parsed['module']);
        $this->assertEquals('roles.manage', $parsed['action']);
        $this->assertNull($parsed['scope']);
    }

    public function test_parse_handles_keys_with_single_action(): void
    {
        $parsed = PermissionKey::parse('org.branches.view.any');

        $this->assertEquals('org', $parsed['module']);
        $this->assertEquals('branches.view', $parsed['action']);
        $this->assertEquals('any', $parsed['scope']);
    }
}
