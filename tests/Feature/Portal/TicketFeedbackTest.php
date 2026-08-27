<?php

namespace Tests\Feature\Portal;

use App\Domains\Customers\Models\Customer;
use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_accepts_score_1_to_5(): void
    {
        $customer = Customer::forceCreate(['id' => 5, 'uuid' => Str::uuid(), 'name' => 'Test Customer', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer->id,
            'email_verified_at' => now(),
        ]);

        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);
        $token = $account->createToken('portal', ['portal'])->plainTextToken;

        for ($score = 1; $score <= 5; $score++) {
            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson("/api/v1/portal/tickets/{$ticket->uuid}/feedback", [
                    'score' => $score,
                    'comment' => 'Test comment',
                ]);

            if ($score > 1) {
                $response->assertStatus(409);
                break;
            }

            $response->assertStatus(201);
        }
    }

    public function test_feedback_rejects_score_outside_1_to_5(): void
    {
        $customer = Customer::forceCreate(['id' => 6, 'uuid' => Str::uuid(), 'name' => 'Test Customer', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer->id,
            'email_verified_at' => now(),
        ]);

        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);
        $token = $account->createToken('portal', ['portal'])->plainTextToken;

        foreach ([0, 6, -1] as $score) {
            $this->withHeader('Authorization', "Bearer $token")
                ->postJson("/api/v1/portal/tickets/{$ticket->uuid}/feedback", [
                    'score' => $score,
                ])
                ->assertStatus(422);
        }
    }

    public function test_second_feedback_returns_409_conflict(): void
    {
        $customer = Customer::forceCreate(['id' => 7, 'uuid' => Str::uuid(), 'name' => 'Test Customer', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer->id,
            'email_verified_at' => now(),
        ]);

        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);
        $token = $account->createToken('portal', ['portal'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/v1/portal/tickets/{$ticket->uuid}/feedback", [
                'score' => 5,
            ])
            ->assertStatus(201);

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/v1/portal/tickets/{$ticket->uuid}/feedback", [
                'score' => 3,
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'portal.feedback_already_submitted');
    }
}
