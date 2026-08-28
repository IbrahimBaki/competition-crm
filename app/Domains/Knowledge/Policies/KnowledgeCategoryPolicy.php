<?php

namespace App\Domains\Knowledge\Policies;

use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KnowledgeCategoryPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_CATEGORIES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function manage(User $user, KnowledgeCategory $category): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_CATEGORIES_MANAGE)
            ? Response::allow()
            : Response::deny();
    }
}
