<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Models\ContactType;

interface TextNormaliser
{
    public function normaliseName(string $value): string;

    public function normaliseContact(string $value, ContactType $type): string;
}
