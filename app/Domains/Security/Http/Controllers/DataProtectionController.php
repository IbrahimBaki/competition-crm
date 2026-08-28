<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\AnonymisePersonalData;
use App\Domains\Security\Http\Requests\EraseUserDataRequest;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataProtectionController extends Controller
{
    use AuthorizesRequests;

    public function erase(EraseUserDataRequest $request, User $user, AnonymisePersonalData $action)
    {
        $action->execute($request->user(), $user, $request->input('reason'));

        return ApiResponse::noContent();
    }

    public function retention(Request $request)
    {
        $this->authorize('view', User::class);

        $config = config('retention.classes', []);
        $minimumDays = config('retention.audit_minimum_days', 365);

        $data = [];
        foreach ($config as $dataClass => $settings) {
            $days = $settings['days'] ?? null;
            $cutoff = $days !== null ? now()->subDays($days)->toIso8601String() : null;

            $data[$dataClass] = [
                'days' => $days,
                'cutoff' => $cutoff,
            ];
        }

        return ApiResponse::success([
            'classes' => $data,
            'audit_minimum_days' => $minimumDays,
        ]);
    }
}
