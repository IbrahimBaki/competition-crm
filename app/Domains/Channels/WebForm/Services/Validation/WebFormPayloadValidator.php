<?php

namespace App\Domains\Channels\WebForm\Services\Validation;

use App\Domains\Channels\WebForm\Exceptions\WebFormValidationFailedException;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormFieldType;
use App\Domains\Customers\Services\TextNormaliser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class WebFormPayloadValidator
{
    public function __construct(
        private readonly TextNormaliser $textNormaliser
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed> Normalised, whitelisted answers.
     *
     * @throws WebFormValidationFailedException
     */
    public function validate(WebForm $form, array $payload): array
    {
        $normalised = [];
        $errors = [];

        // Check for unknown keys
        $declaredKeys = $form->fields->pluck('key')->toArray();
        $payloadKeys = array_keys($payload);
        $unknownKeys = array_diff($payloadKeys, $declaredKeys);

        if (! empty($unknownKeys)) {
            foreach ($unknownKeys as $key) {
                $errors[$key][] = __('errors.channels.web_form.validation_failed');
            }
        }

        // Validate and normalise each field
        foreach ($form->fields as $field) {
            $value = $payload[$field->key] ?? null;

            try {
                $normalised[$field->key] = $this->normaliseField($field, $value);
            } catch (ValidationException $e) {
                foreach ($e->errors() as $fieldErrors) {
                    $errors[$field->key] = array_merge($errors[$field->key] ?? [], $fieldErrors);
                }
            }
        }

        if (! empty($errors)) {
            throw new WebFormValidationFailedException($errors);
        }

        return $normalised;
    }

    /**
     * @return mixed Normalised field value
     *
     * @throws ValidationException
     */
    private function normaliseField($field, mixed $value): mixed
    {
        // Treat whitespace-only strings as absent
        if (is_string($value)) {
            $trimmed = trim($value);
            if (empty($trimmed)) {
                $value = null;
            } else {
                $value = $trimmed;
            }
        }

        // Check required
        if ($field->is_required && $value === null) {
            throw ValidationException::withMessages([
                $field->key => [__('validation.required')],
            ]);
        }

        if ($value === null) {
            return null;
        }

        // Apply type-specific rules
        $rules = [];

        switch ($field->type) {
            case WebFormFieldType::Email:
                $rules[] = 'email:rfc,dns';
                break;

            case WebFormFieldType::Phone:
                // Normalise phone through the existing service
                try {
                    $value = $this->textNormaliser->normaliseContact($value, ContactType::Phone);
                } catch (\Exception) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.phone')],
                    ]);
                }
                break;

            case WebFormFieldType::Number:
                $rules[] = 'numeric';
                break;

            case WebFormFieldType::Date:
                $rules[] = 'date:Y-m-d';
                break;

            case WebFormFieldType::Select:
                if (! empty($field->options) && is_array($field->options)) {
                    $rules[] = 'in:'.implode(',', $field->options);
                }
                break;

            case WebFormFieldType::MultiSelect:
                if (! empty($field->options) && is_array($field->options)) {
                    $rules[] = 'array';
                    $rules[] = 'in:'.implode(',', $field->options);
                }
                break;

            case WebFormFieldType::Boolean:
                $rules[] = 'boolean';
                $value = (bool) $value;
                break;
        }

        // Check string length ceiling for text fields
        if (in_array($field->type, [
            WebFormFieldType::Text,
            WebFormFieldType::TextArea,
            WebFormFieldType::Email,
            WebFormFieldType::Phone,
        ])) {
            $maxLength = config('channels.web_form.max_field_length', 5000);
            if (is_string($value) && mb_strlen($value) > $maxLength) {
                throw ValidationException::withMessages([
                    $field->key => [__('validation.max.string', ['max' => $maxLength])],
                ]);
            }
        }

        // Apply field-specific validation rules
        if (! empty($field->validation)) {
            if (isset($field->validation['min'])) {
                $min = $field->validation['min'];
                if (is_string($value) && mb_strlen($value) < $min) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.min.string', ['min' => $min])],
                    ]);
                } elseif (is_numeric($value) && $value < $min) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.min.numeric', ['min' => $min])],
                    ]);
                }
            }

            if (isset($field->validation['max'])) {
                $max = $field->validation['max'];
                if (is_string($value) && mb_strlen($value) > $max) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.max.string', ['max' => $max])],
                    ]);
                } elseif (is_numeric($value) && $value > $max) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.max.numeric', ['max' => $max])],
                    ]);
                }
            }

            if (isset($field->validation['pattern']) && is_string($value)) {
                $pattern = $field->validation['pattern'];
                if (! preg_match("/$pattern/", $value)) {
                    throw ValidationException::withMessages([
                        $field->key => [__('validation.regex')],
                    ]);
                }
            }
        }

        // Run Laravel validator for the aggregated rules
        if (! empty($rules)) {
            $validator = Validator::make([$field->key => $value], [
                $field->key => $rules,
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $value = $validator->validated()[$field->key];
        }

        return $value;
    }
}
