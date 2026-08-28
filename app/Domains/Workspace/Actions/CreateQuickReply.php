<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Workspace\Exceptions\QuickReplyScopeMismatchException;
use App\Domains\Workspace\Exceptions\QuickReplyTitleTakenException;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Models\QuickReplyScope;
use App\Models\User;
use App\Support\I18n\BilingualString;

readonly class CreateQuickReply
{
    public function handle(
        string $scope,
        ?User $owner,
        ?Department $department,
        BilingualString $title,
        BilingualString $body,
        User $creator,
    ): QuickReply {
        $scopeEnum = QuickReplyScope::tryFrom($scope);
        if ($scopeEnum === null) {
            throw new QuickReplyScopeMismatchException;
        }

        // Validate scope/owner/department mismatch
        if ($scopeEnum === QuickReplyScope::Personal) {
            if ($owner === null || $department !== null) {
                throw new QuickReplyScopeMismatchException;
            }
            // For personal scope, check title uniqueness within owner's personal scope
            $existingTitle = QuickReply::query()
                ->where('scope', QuickReplyScope::Personal->value)
                ->where('owner_id', $owner->getKey())
                ->where(function ($query) use ($title) {
                    $query->when($title->ar, fn ($q) => $q->orWhere('title->ar', $title->ar))
                        ->when($title->en, fn ($q) => $q->orWhere('title->en', $title->en));
                })
                ->exists();

            if ($existingTitle) {
                throw new QuickReplyTitleTakenException;
            }
        } elseif ($scopeEnum === QuickReplyScope::Shared) {
            if ($owner !== null) {
                throw new QuickReplyScopeMismatchException;
            }
        }

        $reply = QuickReply::create([
            'scope' => $scopeEnum,
            'owner_id' => $owner?->getKey(),
            'department_id' => $department?->getKey(),
            'title' => $title,
            'body' => $body,
            'is_active' => true,
        ]);

        return $reply->refresh();
    }
}
