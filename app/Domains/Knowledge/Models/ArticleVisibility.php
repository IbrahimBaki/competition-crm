<?php

namespace App\Domains\Knowledge\Models;

enum ArticleVisibility: string
{
    case Public = 'public';
    case Customers = 'customers';
    case Internal = 'internal';
}
