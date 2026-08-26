<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\SavedViewNameTakenException;
use App\Domains\Ticketing\Models\TicketSavedView;
use App\Models\User;

class SaveTicketView
{
    public function handle(
        User $user,
        string $name,
        array $query,
        bool $isShared = false,
    ): TicketSavedView {
        if (TicketSavedView::where('user_id', $user->id)->where('name', $name)->exists()) {
            throw new SavedViewNameTakenException;
        }

        return TicketSavedView::create([
            'user_id' => $user->id,
            'name' => $name,
            'query' => $query,
            'is_shared' => $isShared,
        ]);
    }
}
