<?php

namespace Tests\Feature\Api;

use App\Domains\Customers\Models\Customer;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceBudgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Login as admin for all requests
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@support.local',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
    }

    protected string $token = '';

    public function test_ticket_index_query_count_within_budget()
    {
        // Seed a small dataset
        $customers = Customer::factory(10)->create();
        $tickets = [];
        foreach ($customers as $customer) {
            for ($i = 0; $i < 5; $i++) {
                $tickets[] = Ticket::factory()->create(['customer_id' => $customer->id]);
            }
        }

        DB::enableQueryLog();
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/v1/tickets?page=1&per_page=10');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $queryCount = count($queries);
        $budget = config('performance.budgets.tickets.index.queries');

        $this->assertLessThanOrEqual(
            $budget,
            $queryCount,
            "Ticket index query count ({$queryCount}) exceeds budget ({$budget})"
        );
    }

    public function test_ticket_show_query_count_within_budget()
    {
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);

        DB::enableQueryLog();
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("/api/v1/tickets/{$ticket->id}");
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $queryCount = count($queries);
        $budget = config('performance.budgets.tickets.show.queries');

        $this->assertLessThanOrEqual(
            $budget,
            $queryCount,
            "Ticket show query count ({$queryCount}) exceeds budget ({$budget})"
        );
    }

    public function test_ticket_index_queries_do_not_grow_with_row_count()
    {
        // Test with 5 tickets
        Customer::factory(5)->create()
            ->each(function ($customer) {
                Ticket::factory(5)->create(['customer_id' => $customer->id]);
            });

        DB::enableQueryLog();
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/v1/tickets?page=1&per_page=10');
        $queriesSmall = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Clean up and test with 50 tickets
        Ticket::truncate();
        Customer::truncate();

        Customer::factory(50)->create()
            ->each(function ($customer) {
                Ticket::factory(5)->create(['customer_id' => $customer->id]);
            });

        DB::enableQueryLog();
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/v1/tickets?page=1&per_page=10');
        $queriesLarge = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Query count should not grow significantly with more rows
        $this->assertLessThanOrEqual(
            $queriesSmall + 5, // Allow small variance
            $queriesLarge,
            "N+1 detected: query count grew from {$queriesSmall} to {$queriesLarge} with more rows"
        );
    }

    public function test_ticket_show_queries_do_not_grow_with_row_count()
    {
        // Test with small dataset
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);

        DB::enableQueryLog();
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("/api/v1/tickets/{$ticket->id}");
        $queriesSmall = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Add more unrelated tickets
        Customer::factory(100)->create()
            ->each(function ($c) {
                Ticket::factory(10)->create(['customer_id' => $c->id]);
            });

        DB::enableQueryLog();
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("/api/v1/tickets/{$ticket->id}");
        $queriesLarge = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Query count should not grow with unrelated rows
        $this->assertLessThanOrEqual(
            $queriesSmall + 2, // Allow small variance
            $queriesLarge,
            "N+1 detected: query count grew from {$queriesSmall} to {$queriesLarge}"
        );
    }
}
