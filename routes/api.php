<?php

use App\Domains\Organisation\Http\Controllers\BranchController;
use App\Domains\Organisation\Http\Controllers\DepartmentController;
use App\Domains\Organisation\Http\Controllers\TeamController;
use App\Domains\Organisation\Http\Controllers\UserPlacementController;
use App\Domains\Security\Http\Controllers\AuthController;
use App\Domains\Security\Http\Controllers\AuthMeController;
use App\Domains\Security\Http\Controllers\AuthPolicyController;
use App\Domains\Security\Http\Controllers\InvitationController;
use App\Domains\Security\Http\Controllers\PasswordResetController;
use App\Domains\Security\Http\Controllers\PermissionCatalogueController;
use App\Domains\Security\Http\Controllers\RoleController;
use App\Domains\Security\Http\Controllers\TwoFactorController;
use App\Domains\Security\Http\Controllers\UserLifecycleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/two-factor/challenge', [TwoFactorController::class, 'challenge']);
    Route::post('auth/password/forgot', [PasswordResetController::class, 'forgot']);
    Route::post('auth/password/reset', [PasswordResetController::class, 'reset']);
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::get('health/live', [HealthController::class, 'live']);
    Route::get('health/ready', [HealthController::class, 'ready']);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthMeController::class, 'show']);
    Route::get('/permissions/catalogue', [PermissionCatalogueController::class, 'show']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);

    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/users/{user}', [RoleController::class, 'attachUser']);
    Route::delete('roles/{role}/users/{user}', [RoleController::class, 'detachUser']);

    Route::apiResource('branches', BranchController::class);
    Route::post('branches/{branch}/activate', [BranchController::class, 'activate']);
    Route::post('branches/{branch}/deactivate', [BranchController::class, 'deactivate']);

    Route::get('branches/{branch}/working-hours', [BranchWorkingHourController::class, 'index']);
    Route::put('branches/{branch}/working-hours', [BranchWorkingHourController::class, 'update']);

    Route::get('branches/{branch}/holidays', [BranchHolidayController::class, 'index']);
    Route::post('branches/{branch}/holidays', [BranchHolidayController::class, 'store']);
    Route::put('branches/{branch}/holidays/{holiday}', [BranchHolidayController::class, 'update']);
    Route::delete('branches/{branch}/holidays/{holiday}', [BranchHolidayController::class, 'destroy']);

    Route::apiResource('departments', DepartmentController::class);
    Route::post('departments/{department}/activate', [DepartmentController::class, 'activate']);
    Route::post('departments/{department}/deactivate', [DepartmentController::class, 'deactivate']);

    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/activate', [TeamController::class, 'activate']);
    Route::post('teams/{team}/deactivate', [TeamController::class, 'deactivate']);

    Route::post('users/{user}/branches/{branch}', [UserPlacementController::class, 'attachBranch']);
    Route::delete('users/{user}/branches/{branch}', [UserPlacementController::class, 'detachBranch']);
    Route::post('users/{user}/branches/{branch}/primary', [UserPlacementController::class, 'setPrimaryBranch']);
    Route::post('users/{user}/departments/{department}', [UserPlacementController::class, 'attachDepartment']);
    Route::delete('users/{user}/departments/{department}', [UserPlacementController::class, 'detachDepartment']);

    Route::get('users', [UserLifecycleController::class, 'index']);
    Route::post('users/invite', [UserLifecycleController::class, 'invite']);
    Route::post('users/{user}/deactivate', [UserLifecycleController::class, 'deactivate']);
    Route::post('users/{user}/activate', [UserLifecycleController::class, 'activate']);

    Route::post('auth/two-factor', [TwoFactorController::class, 'enable']);
    Route::post('auth/two-factor/confirm', [TwoFactorController::class, 'confirm']);
    Route::delete('auth/two-factor', [TwoFactorController::class, 'disable']);
    Route::post('auth/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes']);

    Route::get('auth/policy', [AuthPolicyController::class, 'show']);
    Route::put('auth/policy', [AuthPolicyController::class, 'update']);
});
