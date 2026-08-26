<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PermissionCatalogueController extends Controller
{
    public function show(): JsonResponse
    {
        $keys = PermissionKey::all();
        $grouped = [];

        foreach ($keys as $key) {
            $parsed = PermissionKey::parse($key);
            $module = $parsed['module'];

            if (! isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = [
                'key' => $key,
                'action' => $parsed['action'],
                'scope' => $parsed['scope'],
            ];
        }

        ksort($grouped);

        return response()->json([
            'data' => $grouped,
        ]);
    }
}
