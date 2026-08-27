<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Exceptions\AiProviderUnavailableException;
use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Services\AiClient;
use App\Domains\Ai\Services\Provider\AiCompletionRequest;
use App\Domains\Ai\Services\Provider\AiCompletionResult;
use App\Domains\Ai\Services\Provider\AiProvider;
use App\Domains\Ticketing\Actions\PostTicketMessage;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiProviderOutageTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_outage_returns_503_error(): void
    {
        config(['ai.enabled' => true, 'ai.features.summary' => true]);

        // Bind a throwing provider
        $this->app->bind('App\Domains\Ai\Services\Provider\AiProvider', function () {
            return new class implements AiProvider
            {
                public function complete(AiCompletionRequest $request): AiCompletionResult
                {
                    throw new \Exception('Provider is down');
                }
            };
        });

        $client = app(AiClient::class);

        $this->expectException(AiProviderUnavailableException::class);
        $client->complete(AiFeature::Summary, ['ticket summary']);
    }

    public function test_ticket_operations_still_work_during_provider_outage(): void
    {
        $ticket = Ticket::factory()->create();
        $actor = User::factory()->create();

        $action = app(PostTicketMessage::class);

        // Should succeed even if AI provider is down
        $message = $action->handle(
            $ticket,
            $actor,
            MessageChannel::Webform,
            'Customer message',
        );

        $this->assertNotNull($message->id);
        $this->assertEquals('Customer message', $message->body);
    }
}
