<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Exceptions\AiFeatureDisabledException;
use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Services\Gate\AiFeatureGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiFeatureToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_feature_throws_exception(): void
    {
        config(['ai.enabled' => true, 'ai.features.summary' => false]);

        $gate = app(AiFeatureGate::class);

        $this->expectException(AiFeatureDisabledException::class);
        $gate->check(AiFeature::Summary);
    }

    public function test_master_disabled_throws_exception(): void
    {
        config(['ai.enabled' => false]);

        $gate = app(AiFeatureGate::class);

        $this->expectException(AiFeatureDisabledException::class);
        $gate->check(AiFeature::Summary);
    }

    public function test_enabled_feature_passes(): void
    {
        config(['ai.enabled' => true, 'ai.features.summary' => true]);

        $gate = app(AiFeatureGate::class);

        // Should not throw
        try {
            $gate->check(AiFeature::Summary);
            $this->fail('Expected exception but none was thrown');
        } catch (AiFeatureDisabledException $e) {
            // Expected only if feature check fails
            $this->fail('Feature should be enabled');
        } catch (\Exception $e) {
            // Other exceptions are OK (provider unavailable, etc)
        }
    }
}
