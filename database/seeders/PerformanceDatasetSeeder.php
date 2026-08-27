<?php

namespace Database\Seeders;

use App\Domains\Customers\Models\Customer;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Database\Seeder;

class PerformanceDatasetSeeder extends Seeder
{
    public function run(): void
    {
        // Production guard: refuse to run in production
        if (app()->environment('production')) {
            throw new \Exception(
                'Performance dataset seeder is destructive and must not run in production. '
                .'Use only with `migrate:fresh --seed` in development/testing environments.'
            );
        }

        $config = config('performance.dataset');

        // Create customers
        $customerCount = $config['customers'] ?? 1000;
        $ticketCount = $config['tickets'] ?? 5000;
        $messagesPerTicket = $config['messages_per_ticket'] ?? 6;

        $this->command->info("Creating {$customerCount} customers...");
        $customers = Customer::factory($customerCount)->create();

        $this->command->info("Creating {$ticketCount} tickets...");
        $ticketChunks = array_chunk(range(1, $ticketCount), 500);

        foreach ($ticketChunks as $chunk) {
            foreach ($chunk as $i) {
                $customer = $customers->random();
                Ticket::factory()->create([
                    'customer_id' => $customer->id,
                    'organisation_id' => $customer->organisation_id,
                    'subject' => 'Test Ticket #'.$i,
                    'reference' => sprintf('TK-%06d', $i),
                    'created_at' => now()->subDays(rand(0, 90)),
                ]);
            }
        }

        // Create messages for each ticket
        $this->command->info("Creating messages ({$messagesPerTicket} per ticket)...");
        $tickets = Ticket::all();
        $messageChunks = array_chunk($tickets->all(), 100);

        foreach ($messageChunks as $chunk) {
            foreach ($chunk as $ticket) {
                for ($i = 0; $i < $messagesPerTicket; $i++) {
                    TicketMessage::factory()->create([
                        'ticket_id' => $ticket->id,
                        'organisation_id' => $ticket->organisation_id,
                        'body' => 'Test message '.$i.' for ticket '.$ticket->reference,
                        'visibility' => $i === 0 ? 'public' : 'internal',
                        'created_at' => $ticket->created_at->addMinutes($i * 30),
                    ]);
                }
            }
        }

        $this->command->info('Performance dataset created successfully');
    }
}
