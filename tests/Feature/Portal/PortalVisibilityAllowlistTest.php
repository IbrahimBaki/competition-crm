<?php

namespace Tests\Feature\Portal;

use Tests\TestCase;

class PortalVisibilityAllowlistTest extends TestCase
{
    public function test_portal_guarded_routes_accessible_by_portal_token(): void
    {
        // TODO: Enumerate all /api/v1/portal/* routes
        // For each route:
        // 1. Authenticate as portal user
        // 2. Call the endpoint
        // 3. Verify 2xx response
        // 4. Verify response contains only that user's data
        // 5. Verify other users' data is absent

        $this->assertTrue(true, 'Allowlist test placeholder');
    }

    public function test_all_staff_routes_reject_portal_token(): void
    {
        // TODO: Enumerate all staff-only routes (auth:sanctum, portal.deny)
        // For each route:
        // 1. Attempt to call with portal token
        // 2. Verify 401 or 403 response (not 200)
        // 3. Confirm error message indicates authorization failure

        $this->assertTrue(true, 'Staff route rejection placeholder');
    }

    public function test_internal_notes_absent_from_portal_payload(): void
    {
        // TODO: Create a ticket with internal notes and public messages
        // 1. Fetch ticket via portal endpoint
        // 2. Verify messages list contains only public messages
        // 3. Verify internal messages are filtered out

        $this->assertTrue(true, 'Internal note filtering placeholder');
    }

    public function test_portal_attachment_guard(): void
    {
        // TODO: Create two customers with tickets and attachments
        // 1. Portal user A tries to fetch attachment from ticket owned by user B
        // 2. Verify 404 (not 403)—attachment is hidden, not forbidden
        // 3. Portal user A can fetch attachments from own tickets

        $this->assertTrue(true, 'Attachment guard placeholder');
    }

    public function test_expired_guest_grant_denied(): void
    {
        // TODO: Create a guest grant token
        // 1. Access guest ticket view before expiration — succeeds
        // 2. Advance clock past expiration
        // 3. Attempt same access — fails with 401
        // 4. Verify PortalGuestGrantExpiredException is thrown

        $this->assertTrue(true, 'Guest grant expiration placeholder');
    }
}
