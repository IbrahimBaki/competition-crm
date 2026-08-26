<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Ticketing\Exceptions\InvalidTicketCustomFieldException;
use App\Domains\Ticketing\Models\TicketCategory;

class TicketCustomFieldValidator
{
    public function __construct(
        private readonly TicketCategoryTree $categoryTree,
    ) {}

    /**
     * Validate custom fields against a category and return sanitised values.
     *
     * @throws InvalidTicketCustomFieldException
     */
    public function validate(?TicketCategory $category, ?array $values): array
    {
        if ($category === null) {
            return empty($values) ? [] : [];
        }

        $values ??= [];
        $definitions = $this->categoryTree->resolveFieldDefinitions($category);
        $sanitised = [];

        foreach ($definitions as $key => $definition) {
            if (! array_key_exists($key, $values)) {
                if ($definition->is_required) {
                    throw InvalidTicketCustomFieldException::missingRequired($key);
                }

                continue;
            }

            $value = $values[$key];

            if (! $this->validateType($value, $definition->type)) {
                throw InvalidTicketCustomFieldException::typeMismatch($key);
            }

            if ($definition->type === 'select') {
                $options = $definition->options ?? [];
                if (! in_array($value, $options, strict: true)) {
                    throw InvalidTicketCustomFieldException::invalidSelectValue($key);
                }
            }

            $sanitised[$key] = $value;
        }

        foreach (array_keys($values) as $key) {
            if (! isset($definitions[$key])) {
                throw InvalidTicketCustomFieldException::unknownField($key);
            }
        }

        return $sanitised;
    }

    private function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'text' => is_string($value),
            'number' => is_numeric($value),
            'date' => is_string($value) && strtotime($value) !== false,
            'select' => is_string($value),
            'boolean' => is_bool($value),
            default => false,
        };
    }
}
