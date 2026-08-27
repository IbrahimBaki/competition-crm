<?php

namespace Tests\Unit\Knowledge;

use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Services\Lifecycle\ArticleTransitionMap;
use Tests\TestCase;

class ArticleTransitionMapTest extends TestCase
{
    private ArticleTransitionMap $transitionMap;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transitionMap = new ArticleTransitionMap;
    }

    public function test_draft_can_transition_to_in_review(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::Draft, ArticleState::InReview)
        );
    }

    public function test_draft_can_transition_to_archived(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::Draft, ArticleState::Archived)
        );
    }

    public function test_draft_cannot_transition_to_published(): void
    {
        $this->assertFalse(
            $this->transitionMap->allows(ArticleState::Draft, ArticleState::Published)
        );
    }

    public function test_in_review_can_transition_to_draft(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::InReview, ArticleState::Draft)
        );
    }

    public function test_in_review_can_transition_to_published(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::InReview, ArticleState::Published)
        );
    }

    public function test_published_cannot_transition_to_draft(): void
    {
        $this->assertFalse(
            $this->transitionMap->allows(ArticleState::Published, ArticleState::Draft)
        );
    }

    public function test_published_can_transition_to_archived(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::Published, ArticleState::Archived)
        );
    }

    public function test_archived_can_transition_to_draft(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::Archived, ArticleState::Draft)
        );
    }

    public function test_allowed_from_returns_array_of_valid_targets(): void
    {
        $allowed = $this->transitionMap->allowedFrom(ArticleState::Draft);

        $this->assertCount(2, $allowed);
        $this->assertContains(ArticleState::InReview, $allowed);
        $this->assertContains(ArticleState::Archived, $allowed);
    }

    public function test_same_state_is_always_allowed(): void
    {
        $this->assertTrue(
            $this->transitionMap->allows(ArticleState::Draft, ArticleState::Draft)
        );
    }
}
