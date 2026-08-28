<?php

namespace Tests\Unit\Channels\WebForm;

use App\Domains\Channels\WebForm\Exceptions\WebFormValidationFailedException;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormField;
use App\Domains\Channels\WebForm\Models\WebFormFieldType;
use App\Domains\Channels\WebForm\Services\Validation\WebFormPayloadValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFormPayloadValidatorTest extends TestCase
{
    use RefreshDatabase;

    private WebFormPayloadValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = app(WebFormPayloadValidator::class);
    }

    public function test_validates_required_field(): void
    {
        $form = $this->createForm();
        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'required_field',
            'is_required' => true,
            'type' => WebFormFieldType::Text,
        ]);

        $this->expectException(WebFormValidationFailedException::class);
        $this->validator->validate($form, ['required_field' => '']);
    }

    public function test_rejects_unknown_keys(): void
    {
        $form = $this->createForm();

        $this->expectException(WebFormValidationFailedException::class);
        $this->validator->validate($form, [
            'name' => 'John',
            'unknown_key' => 'Should fail',
        ]);
    }

    public function test_validates_email_type(): void
    {
        $form = $this->createForm();
        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'email',
            'type' => WebFormFieldType::Email,
        ]);

        $this->expectException(WebFormValidationFailedException::class);
        $this->validator->validate($form, ['email' => 'not-an-email']);
    }

    public function test_enforces_max_field_length(): void
    {
        $form = $this->createForm();
        WebFormField::factory()->create([
            'web_form_id' => $form->id,
            'key' => 'text',
            'type' => WebFormFieldType::Text,
        ]);

        $longText = str_repeat('a', config('channels.web_form.max_field_length', 5000) + 1);

        $this->expectException(WebFormValidationFailedException::class);
        $this->validator->validate($form, ['text' => $longText]);
    }

    private function createForm(): WebForm
    {
        return WebForm::factory()->create();
    }
}
