<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ai\Services\Retention\AiSuggestionPurgeHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiRetentionPurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_old_accepted_suggestions(): void
    {
        $oldSuggestion = AiSuggestion::factory()->accepted()->create([
            'created_at' => now()->subDays(100),
        ]);

        $newSuggestion = AiSuggestion::factory()->accepted()->create([
            'created_at' => now(),
        ]);

        $handler = new AiSuggestionPurgeHandler;
        $handler->purge(90);

        $this->assertDatabaseMissing('ai_suggestions', ['id' => $oldSuggestion->id]);
        $this->assertDatabaseHas('ai_suggestions', ['id' => $newSuggestion->id]);
    }

    public function test_preserves_pending_suggestions(): void
    {
        $oldPending = AiSuggestion::factory()->create([
            'state' => AiSuggestionState::Pending,
            'created_at' => now()->subDays(100),
        ]);

        $handler = new AiSuggestionPurgeHandler;
        $handler->purge(90);

        // Pending suggestions should never be purged
        $this->assertDatabaseHas('ai_suggestions', ['id' => $oldPending->id]);
    }
}
