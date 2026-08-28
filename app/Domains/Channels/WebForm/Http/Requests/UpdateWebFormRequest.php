<?php

namespace App\Domains\Channels\WebForm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'array'],
            'title.en' => ['sometimes', 'string'],
            'title.ar' => ['sometimes', 'string'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
            'default_priority' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Validate that at least one identity field is mapped if activating the form
        if ($this->boolean('is_active') && ! $this->hasIdentityField()) {
            $this->validator->after(function ($validator) {
                $validator->errors()->add('is_active', 'Form must have at least one email or phone field mapped to activate');
            });
        }
    }

    private function hasIdentityField(): bool
    {
        $form = $this->route('webForm');

        return $form->fields->contains(fn ($field) => in_array($field->maps_to, ['email', 'phone']));
    }
}
