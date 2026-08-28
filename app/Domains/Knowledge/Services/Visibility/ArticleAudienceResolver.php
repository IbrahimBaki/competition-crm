<?php

namespace App\Domains\Knowledge\Services\Visibility;

use App\Domains\Knowledge\Models\Audience;
use Illuminate\Contracts\Auth\Authenticatable;

class ArticleAudienceResolver
{
    public function resolve(?Authenticatable $user, bool $isStaffContext): Audience
    {
        if ($isStaffContext) {
            return Audience::Staff;
        }

        if ($user !== null) {
            return Audience::Customer;
        }

        return Audience::Anonymous;
    }
}
