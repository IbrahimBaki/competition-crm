<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Exceptions\TicketCategoryDepthExceededException;
use App\Domains\Ticketing\Services\TicketCategoryTree;
use Database\Factories\TicketCategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCategoryTreeTest extends TestCase
{
    use RefreshDatabase;

    private TicketCategoryTree $tree;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tree = app(TicketCategoryTree::class);
    }

    public function test_max_depth_is_3(): void
    {
        $this->assertEquals(3, $this->tree->maxDepth());
    }

    public function test_allows_nesting_up_to_depth_3(): void
    {
        $depth1 = TicketCategoryFactory::new()->create();
        $this->tree->assertCanNest($depth1);

        $depth2 = TicketCategoryFactory::new(['parent_id' => $depth1->id, 'depth' => 2])->create();
        $this->tree->assertCanNest($depth2);

        $depth3 = TicketCategoryFactory::new(['parent_id' => $depth2->id, 'depth' => 3])->create();
        $this->tree->assertCanNest($depth3);
    }

    public function test_rejects_nesting_at_depth_3(): void
    {
        $depth3 = TicketCategoryFactory::new(['depth' => 3])->create();

        $this->expectException(TicketCategoryDepthExceededException::class);
        $this->tree->assertCanNest($depth3);
    }

    public function test_allows_null_parent(): void
    {
        $this->tree->assertCanNest(null);
    }

    public function test_resolves_field_definitions_merges_ancestor_fields(): void
    {
        $root = TicketCategoryFactory::new()->create();
        $root->fields()->create([
            'key' => 'field1',
            'label' => ['en' => 'Field 1', 'ar' => 'الحقل 1'],
            'type' => 'text',
            'is_required' => true,
        ]);

        $child = TicketCategoryFactory::new(['parent_id' => $root->id, 'depth' => 2])->create();
        $child->fields()->create([
            'key' => 'field2',
            'label' => ['en' => 'Field 2', 'ar' => 'الحقل 2'],
            'type' => 'select',
            'is_required' => false,
        ]);

        $fields = $this->tree->resolveFieldDefinitions($child);

        $this->assertCount(2, $fields);
        $this->assertTrue($fields->has('field1'));
        $this->assertTrue($fields->has('field2'));
    }

    public function test_child_field_overrides_ancestor_field(): void
    {
        $root = TicketCategoryFactory::new()->create();
        $root->fields()->create([
            'key' => 'shared',
            'label' => ['en' => 'Root', 'ar' => 'جذر'],
            'type' => 'text',
            'is_required' => false,
        ]);

        $child = TicketCategoryFactory::new(['parent_id' => $root->id, 'depth' => 2])->create();
        $child->fields()->create([
            'key' => 'shared',
            'label' => ['en' => 'Child', 'ar' => 'فرع'],
            'type' => 'select',
            'is_required' => true,
        ]);

        $fields = $this->tree->resolveFieldDefinitions($child);

        $field = $fields['shared'];
        $this->assertEquals('Child', $field->label['en']);
        $this->assertTrue($field->is_required);
    }
}
