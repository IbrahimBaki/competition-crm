<?php

namespace App\Domains\Knowledge\Policies;

use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KnowledgeArticlePolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, KnowledgeArticle $article): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_CREATE)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, KnowledgeArticle $article): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_UPDATE)
            ? Response::allow()
            : Response::deny();
    }

    public function publish(User $user, KnowledgeArticle $article): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_PUBLISH)
            ? Response::allow()
            : Response::deny();
    }

    public function archive(User $user, KnowledgeArticle $article): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_ARCHIVE)
            ? Response::allow()
            : Response::deny();
    }

    public function restoreVersion(User $user, KnowledgeArticle $article): Response
    {
        return $user->hasPermission(PermissionKey::KNOWLEDGE_ARTICLES_VERSIONS_RESTORE)
            ? Response::allow()
            : Response::deny();
    }
}
