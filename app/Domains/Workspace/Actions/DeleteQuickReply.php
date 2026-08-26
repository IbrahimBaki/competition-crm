<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Models\QuickReply;
use App\Models\User;

readonly class DeleteQuickReply
{
    public function handle(QuickReply $reply, User $actor): void
    {
        $reply->delete();
    }
}
