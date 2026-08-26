<?php

use App\Domains\Organisation\Http\Controllers\BranchController;
use App\Domains\Organisation\Http\Controllers\DepartmentController;
use App\Domains\Organisation\Http\Controllers\TeamController;
use App\Domains\Organisation\Http\Controllers\UserPlacementController;
use App\Domains\Security\Http\Controllers\AuthMeController;
use App\Domains\Security\Http\Controllers\PermissionCatalogueController;
use App\Domains\Security\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/auth/me', [AuthMeController::class, 'show']);
    Route::get('/permissions/catalogue', [PermissionCatalogueController::class, 'show']);

    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/users/{user}', [RoleController::class, 'attachUser']);
    Route::delete('roles/{role}/users/{user}', [RoleController::class, 'detachUser']);

    Route::apiResource('branches', BranchController::class);
    Route::post('branches/{branch}/activate', [BranchController::class, 'activate']);
    Route::post('branches/{branch}/deactivate', [BranchController::class, 'deactivate']);

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
});
