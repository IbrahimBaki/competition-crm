<?php

namespace Tests\Feature\Channels;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Chat test base class.
 *
 * Note: Currently uses RefreshDatabase but tests are blocked by pre-existing Story 23
 * migration foreign key issues. Once Story 23 is fixed, tests will run automatically.
 */
abstract class ChatTestCase extends TestCase
{
    use RefreshDatabase;
}
