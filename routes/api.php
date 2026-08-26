<?php

use App\Domains\Customers\Http\Controllers\CustomerAttachmentController;
use App\Domains\Customers\Http\Controllers\CustomerBlockController;
use App\Domains\Customers\Http\Controllers\CustomerContactController;
use App\Domains\Customers\Http\Controllers\CustomerController;
use App\Domains\Customers\Http\Controllers\CustomerDuplicateController;
use App\Domains\Customers\Http\Controllers\CustomerMergeController;
use App\Domains\Customers\Http\Controllers\CustomerNoteController;
use App\Domains\Customers\Http\Controllers\CustomerTimelineController;
use App\Domains\Organisation\Http\Controllers\BranchController;
use App\Domains\Organisation\Http\Controllers\BranchHolidayController;
use App\Domains\Organisation\Http\Controllers\BranchWorkingHourController;
use App\Domains\Organisation\Http\Controllers\DepartmentController;
use App\Domains\Organisation\Http\Controllers\TeamController;
use App\Domains\Organisation\Http\Controllers\UserPlacementController;
use App\Domains\Security\Http\Controllers\AuditLogController;
use App\Domains\Security\Http\Controllers\AuthController;
use App\Domains\Security\Http\Controllers\AuthMeController;
use App\Domains\Security\Http\Controllers\AuthPolicyController;
use App\Domains\Security\Http\Controllers\DataProtectionController;
use App\Domains\Security\Http\Controllers\InvitationController;
use App\Domains\Security\Http\Controllers\PasswordResetController;
use App\Domains\Security\Http\Controllers\PermissionCatalogueController;
use App\Domains\Security\Http\Controllers\RoleController;
use App\Domains\Security\Http\Controllers\TwoFactorController;
use App\Domains\Security\Http\Controllers\UserLifecycleController;
use App\Domains\Ticketing\Http\Controllers\TicketCategoryController;
use App\Domains\Ticketing\Http\Controllers\TicketController;
use App\Domains\Ticketing\Http\Controllers\TicketLifecycleController;
use App\Domains\Ticketing\Http\Controllers\TicketLinkController;
use App\Domains\Ticketing\Http\Controllers\TicketMergeController;
use App\Domains\Ticketing\Http\Controllers\TicketStatusController;
use App\Support\Attachments\Http\AttachmentController;
use App\Support\Http\Health\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->middleware(['throttle:public', 'bot.protect'])->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/two-factor/challenge', [TwoFactorController::class, 'challenge']);
    Route::post('auth/password/forgot', [PasswordResetController::class, 'forgot']);
    Route::post('auth/password/reset', [PasswordResetController::class, 'reset']);
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept']);
});

Route::prefix('v1')->group(function () {
    Route::get('health/live', [HealthController::class, 'live']);
    Route::get('health/ready', [HealthController::class, 'ready']);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthMeController::class, 'show']);
    Route::get('/permissions/catalogue', [PermissionCatalogueController::class, 'show']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);

    Route::post('attachments', [AttachmentController::class, 'store'])->middleware('throttle:uploads');
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show']);

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
    Route::post('users/{user}/erase-personal-data', [DataProtectionController::class, 'erase'])->middleware('idempotency');
    Route::get('data-protection/retention', [DataProtectionController::class, 'retention']);

    Route::post('auth/two-factor', [TwoFactorController::class, 'enable']);
    Route::post('auth/two-factor/confirm', [TwoFactorController::class, 'confirm']);
    Route::delete('auth/two-factor', [TwoFactorController::class, 'disable']);
    Route::post('auth/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes']);

    Route::get('auth/policy', [AuthPolicyController::class, 'show']);
    Route::put('auth/policy', [AuthPolicyController::class, 'update']);

    Route::get('customers/duplicates', [CustomerDuplicateController::class, 'index']);
    Route::post('customers/duplicates/{candidate}/dismiss', [CustomerDuplicateController::class, 'dismiss']);

    Route::apiResource('customers', CustomerController::class)->except(['destroy']);
    Route::post('customers/{customer}/block', [CustomerBlockController::class, 'block']);
    Route::post('customers/{customer}/unblock', [CustomerBlockController::class, 'unblock']);

    Route::get('customers/{customer}/contacts', [CustomerContactController::class, 'index']);
    Route::post('customers/{customer}/contacts', [CustomerContactController::class, 'store']);
    Route::put('customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'update']);
    Route::delete('customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy']);

    Route::get('customers/{customer}/notes', [CustomerNoteController::class, 'index']);
    Route::post('customers/{customer}/notes', [CustomerNoteController::class, 'store']);
    Route::delete('customers/{customer}/notes/{note}', [CustomerNoteController::class, 'destroy']);

    Route::get('customers/{customer}/attachments', [CustomerAttachmentController::class, 'index']);
    Route::post('customers/{customer}/attachments', [CustomerAttachmentController::class, 'store']);
    Route::delete('customers/{customer}/attachments/{attachment}', [CustomerAttachmentController::class, 'destroy']);

    Route::get('customers/{customer}/timeline', [CustomerTimelineController::class, 'index']);
    Route::post('customers/{customer}/merge', [CustomerMergeController::class, 'store']);

    Route::get('ticket-statuses', [TicketStatusController::class, 'index']);
    Route::post('ticket-statuses', [TicketStatusController::class, 'store'])->middleware('idempotency');
    Route::patch('ticket-statuses/{status}', [TicketStatusController::class, 'update'])->middleware('idempotency');

    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store'])->middleware('idempotency');

    Route::get('tickets/queues/mine', [TicketQueueController::class, 'mine']);
    Route::get('tickets/queues/department/{department}', [TicketQueueController::class, 'department']);

    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::patch('tickets/{ticket}', [TicketController::class, 'update'])->middleware('idempotency');
    Route::get('tickets/{ticket}/history', [TicketController::class, 'history']);

    Route::post('tickets/{ticket}/status', [TicketLifecycleController::class, 'status'])->middleware('idempotency');
    Route::post('tickets/{ticket}/reopen', [TicketLifecycleController::class, 'reopen'])->middleware('idempotency');
    Route::post('tickets/{ticket}/spam', [TicketLifecycleController::class, 'markSpam'])->middleware('idempotency');
    Route::delete('tickets/{ticket}/spam', [TicketLifecycleController::class, 'restoreFromSpam'])->middleware('idempotency');

    Route::post('tickets/{ticket}/merge', [TicketMergeController::class, 'merge'])->middleware('idempotency');
    Route::post('tickets/{ticket}/split', [TicketMergeController::class, 'split'])->middleware('idempotency');

    Route::post('tickets/{ticket}/assign', [TicketAssignmentController::class, 'assign'])->middleware('idempotency');
    Route::delete('tickets/{ticket}/assign', [TicketAssignmentController::class, 'unassign'])->middleware('idempotency');
    Route::post('tickets/{ticket}/claim', [TicketAssignmentController::class, 'claim'])->middleware('idempotency');
    Route::post('tickets/{ticket}/transfer/agent', [TicketAssignmentController::class, 'transferToAgent'])->middleware('idempotency');
    Route::post('tickets/{ticket}/transfer/department', [TicketAssignmentController::class, 'transferToDepartment'])->middleware('idempotency');

    Route::get('tickets/{ticket}/links', [TicketLinkController::class, 'index']);
    Route::post('tickets/{ticket}/links', [TicketLinkController::class, 'store'])->middleware('idempotency');
    Route::delete('tickets/{ticket}/links/{link}', [TicketLinkController::class, 'destroy']);

    Route::get('tickets/{ticket}/messages', [TicketMessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->middleware('idempotency');
    Route::get('tickets/{ticket}/messages/{message}/delivery-events', [TicketMessageController::class, 'deliveryEvents']);
    Route::post('tickets/{ticket}/messages/{message}/retry', [TicketMessageController::class, 'retry'])->middleware('idempotency');

    Route::get('ticket-categories', [TicketCategoryController::class, 'index']);
    Route::post('ticket-categories', [TicketCategoryController::class, 'store'])->middleware('idempotency');
    Route::patch('ticket-categories/{category}', [TicketCategoryController::class, 'update'])->middleware('idempotency');
    Route::delete('ticket-categories/{category}', [TicketCategoryController::class, 'destroy']);
});
