<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Actions\CompleteTwoFactorChallenge;
use App\Domains\Security\Actions\ConfirmTwoFactor;
use App\Domains\Security\Actions\DisableTwoFactor;
use App\Domains\Security\Actions\EnableTwoFactor;
use App\Domains\Security\Http\Requests\ConfirmTwoFactorRequest;
use App\Domains\Security\Http\Requests\DisableTwoFactorRequest;
use App\Domains\Security\Http\Requests\EnableTwoFactorRequest;
use App\Domains\Security\Http\Requests\TwoFactorChallengeRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TwoFactorController extends Controller
{
    public function enable(EnableTwoFactorRequest $request, EnableTwoFactor $action)
    {
        $result = $action->execute();

        $request->session()->put([
            'two_factor_secret' => $result['secret'],
            'two_factor_recovery_codes' => $result['recovery_codes'],
        ]);

        return response()->json([
            'data' => [
                'qr_code' => $result['qr_code'],
            ],
            'meta' => [
                'recovery_codes' => $result['recovery_codes'],
            ],
        ]);
    }

    public function confirm(ConfirmTwoFactorRequest $request, ConfirmTwoFactor $action)
    {
        $secret = $request->session()->pull('two_factor_secret');
        $recoveryCodes = $request->session()->pull('two_factor_recovery_codes');

        if (! $secret || ! $recoveryCodes) {
            return response()->json([
                'error' => [
                    'code' => 'invalid_state',
                    'message' => 'Two-factor setup not in progress.',
                ],
            ], 422);
        }

        $action->execute($request->user(), $secret, $request->input('code'), $recoveryCodes);

        return response()->json(status: 204);
    }

    public function disable(DisableTwoFactorRequest $request, DisableTwoFactor $action)
    {
        $action->execute($request->user(), $request->input('password'));

        return response()->json(status: 204);
    }

    public function challenge(TwoFactorChallengeRequest $request, CompleteTwoFactorChallenge $action)
    {
        $action->execute($request->user(), $request->input('code'));

        return response()->json(status: 204);
    }

    public function recoveryCodes(Request $request)
    {
        $user = $request->user();

        $codes = array_map(fn () => bin2hex(random_bytes(4)), range(1, 10));

        $user->update(['two_factor_recovery_codes' => $codes]);

        return response()->json([
            'meta' => [
                'recovery_codes' => $codes,
            ],
        ]);
    }
}
