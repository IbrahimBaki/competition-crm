<?php

namespace Tests\Unit\Ticketing;

use App\Domains\Ticketing\Exceptions\InvalidTicketCustomFieldException;
use App\Domains\Ticketing\Services\TicketCustomFieldValidator;
use Database\Factories\TicketCategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCustomFieldValidatorTest extends TestCase
{
    use RefreshDatabase;

    private TicketCustomFieldValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = app(TicketCustomFieldValidator::class);
    }

    public function test_validates_required_field(): void
    {
        $category = TicketCategoryFactory::new()->create();
        $category->fields()->create([
            'key' => 'urgent_reason',
            'label' => ['en' => 'Reason', 'ar' => 'السبب'],
            'type' => 'text',
            'is_required' => true,
        ]);

        $this->expectException(InvalidTicketCustomFieldException::class);
        $this->validator->validate($category, []);
    }

    public function test_validates_field_type(): void
    {
        $category = TicketCategoryFactory::new()->create();
        $category->fields()->create([
            'key' => 'count',
            'label' => ['en' => 'Count', 'ar' => 'العدد'],
            'type' => 'number',
            'is_required' => false,
        ]);

        $this->expectException(InvalidTicketCustomFieldException::class);
        $this->validator->validate($category, ['count' => 'not-a-number']);
    }

    public function test_validates_select_options(): void
    {
        $category = TicketCategoryFactory::new()->create();
        $category->fields()->create([
            'key' => 'severity',
            'label' => ['en' => 'Severity', 'ar' => 'الخطورة'],
            'type' => 'select',
            'options' => ['low', 'medium', 'high'],
            'is_required' => false,
        ]);

        $this->expectException(InvalidTicketCustomFieldException::class);
        $this->validator->validate($category, ['severity' => 'critical']);
    }

    public function test_rejects_unknown_field(): void
    {
        $category = TicketCategoryFactory::new()->create();

        $this->expectException(InvalidTicketCustomFieldException::class);
        $this->validator->validate($category, ['unknown_field' => 'value']);
    }

    public function test_validates_optional_fields(): void
    {
        $category = TicketCategoryFactory::new()->create();
        $category->fields()->create([
            'key' => 'notes',
            'label' => ['en' => 'Notes', 'ar' => 'ملاحظات'],
            'type' => 'text',
            'is_required' => false,
        ]);

        $result = $this->validator->validate($category, []);
        $this->assertEmpty($result);
    }

    public function test_accepts_valid_payload(): void
    {
        $category = TicketCategoryFactory::new()->create();
        $category->fields()->create([
            'key' => 'name',
            'label' => ['en' => 'Name', 'ar' => 'الاسم'],
            'type' => 'text',
            'is_required' => true,
        ]);
        $category->fields()->create([
            'key' => 'age',
            'label' => ['en' => 'Age', 'ar' => 'العمر'],
            'type' => 'number',
            'is_required' => false,
        ]);

        $result = $this->validator->validate($category, [
            'name' => 'John',
            'age' => 25,
        ]);

        $this->assertEquals(['name' => 'John', 'age' => 25], $result);
    }

    public function test_returns_empty_when_no_category(): void
    {
        $result = $this->validator->validate(null, []);
        $this->assertEmpty($result);
    }
}
