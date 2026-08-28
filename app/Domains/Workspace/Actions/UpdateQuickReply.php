<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Organisation\Models\Department;
use App\Domains\Workspace\Exceptions\QuickReplyScopeMismatchException;
use App\Domains\Workspace\Exceptions\QuickReplyTitleTakenException;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Models\QuickReplyScope;
use App\Models\User;
use App\Support\I18n\BilingualString;

readonly class UpdateQuickReply
{
    /**
     * @param  array<string, mixed>  $updateData
     */
    public function handle(QuickReply $reply, array $updateData, User $actor): QuickReply
    {
        // Resolve effective scope, owner, and department
        $scope = array_key_exists('scope', $updateData) ? $updateData['scope'] : $reply->scope->value;
        $owner = array_key_exists('owner_id', $updateData)
            ? ($updateData['owner_id'] ? User::findOrFail($updateData['owner_id']) : null)
            : $reply->owner;
        $department = array_key_exists('department_id', $updateData)
            ? ($updateData['department_id'] ? Department::findOrFail($updateData['department_id']) : null)
            : $reply->department;

        $scopeEnum = QuickReplyScope::tryFrom($scope);
        if ($scopeEnum === null) {
            throw new QuickReplyScopeMismatchException;
        }

        // Validate scope/owner/department mismatch
        if ($scopeEnum === QuickReplyScope::Personal) {
            if ($owner === null || $department !== null) {
                throw new QuickReplyScopeMismatchException;
            }
        } elseif ($scopeEnum === QuickReplyScope::Shared) {
            if ($owner !== null) {
                throw new QuickReplyScopeMismatchException;
            }
        }

        // Check title uniqueness if title is being updated
        if (array_key_exists('title', $updateData)) {
            $title = $updateData['title'] instanceof BilingualString
                ? $updateData['title']
                : BilingualString::fromArray($updateData['title']);

            if ($scopeEnum === QuickReplyScope::Personal) {
                /** @var User $owner */
                $existingTitle = QuickReply::query()
                    ->where('scope', QuickReplyScope::Personal->value)
                    ->where('owner_id', $owner->getKey())
                    ->whereKeyNot($reply->getKey())
                    ->where(function ($query) use ($title) {
                        $query->when($title->ar, fn ($q) => $q->orWhere('title->ar', $title->ar))
                            ->when($title->en, fn ($q) => $q->orWhere('title->en', $title->en));
                    })
                    ->exists();

                if ($existingTitle) {
                    throw new QuickReplyTitleTakenException;
                }
            }
        }

        // Build update array with only whitelisted keys
        $allowedKeys = ['scope', 'owner_id', 'department_id', 'title', 'body', 'is_active'];
        $updatePayload = array_intersect_key($updateData, array_flip($allowedKeys));

        // Convert scope to enum if present
        if (array_key_exists('scope', $updatePayload)) {
            $updatePayload['scope'] = $scopeEnum;
        }

        $reply->update($updatePayload);

        return $reply->refresh();
    }
}
