<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Binds Pest-style tests to the project TestCase, which already applies
| RefreshDatabase. Class-based PHPUnit tests inherit the same base directly,
| so both styles run against identical database setup.
|
*/

uses(TestCase::class)->in('Feature', 'Unit', 'Security');
