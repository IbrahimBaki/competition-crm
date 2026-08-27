<?php

use App\Domains\Ai\Http\Controllers\AiSuggestionController;
use App\Domains\Ai\Http\Controllers\AiUsageController;
use App\Domains\Ai\Http\Controllers\TicketAiAssistController;
use App\Domains\Channels\Chat\Http\Controllers\PublicChatSessionController;
use App\Domains\Channels\Messaging\Http\Controllers\ProviderDeliveryReceiptController;
use App\Domains\Channels\Messaging\Http\Controllers\ProviderInboundWebhookController;
use App\Domains\Channels\Messaging\Http\Controllers\ProviderMessageTemplateController;
use App\Domains\Channels\WebForm\Http\Controllers\WebFormController;
use App\Domains\Customers\Http\Controllers\CustomerAttachmentController;
use App\Domains\Customers\Http\Controllers\CustomerBlockController;
use App\Domains\Customers\Http\Controllers\CustomerContactController;
use App\Domains\Customers\Http\Controllers\CustomerController;
use App\Domains\Customers\Http\Controllers\CustomerDuplicateController;
use App\Domains\Customers\Http\Controllers\CustomerMergeController;
use App\Domains\Customers\Http\Controllers\CustomerNoteController;
use App\Domains\Customers\Http\Controllers\CustomerTimelineController;
use App\Domains\Integrations\Http\Controllers\ApiTokenController;
use App\Domains\Integrations\Http\Controllers\CustomerErpContextController;
use App\Domains\Integrations\Http\Controllers\ImportRunController;
use App\Domains\Integrations\Http\Controllers\WebhookDeliveryController;
use App\Domains\Integrations\Http\Controllers\WebhookSubscriptionController;
use App\Domains\Organisation\Http\Controllers\BranchController;
use App\Domains\Organisation\Http\Controllers\BranchHolidayController;
use App\Domains\Organisation\Http\Controllers\BranchWorkingHourController;
use App\Domains\Organisation\Http\Controllers\DepartmentController;
use App\Domains\Organisation\Http\Controllers\TeamController;
use App\Domains\Organisation\Http\Controllers\UserPlacementController;
use App\Domains\Portal\Http\Controllers\GuestTicketTrackingController;
use App\Domains\Portal\Http\Controllers\PortalAccountController;
use App\Domains\Portal\Http\Controllers\PortalAttachmentController;
use App\Domains\Portal\Http\Controllers\PortalAuthController;
use App\Domains\Portal\Http\Controllers\PortalTicketController;
use App\Domains\Portal\Http\Controllers\PortalTicketMessageController;
use App\Domains\Portal\Http\Controllers\TicketFeedbackController;
use App\Domains\Reporting\Http\Controllers\ReportController;
use App\Domains\Reporting\Http\Controllers\ReportExportController;
use App\Domains\Reporting\Http\Controllers\ReportScheduleController;
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
use App\Domains\Sla\Http\Controllers\SlaPolicyController;
use App\Domains\Sla\Http\Controllers\TicketSlaController;
use App\Domains\Ticketing\Http\Controllers\TicketCategoryController;
use App\Domains\Ticketing\Http\Controllers\TicketController;
use App\Domains\Ticketing\Http\Controllers\TicketLifecycleController;
use App\Domains\Ticketing\Http\Controllers\TicketLinkController;
use App\Domains\Ticketing\Http\Controllers\TicketMergeController;
use App\Domains\Ticketing\Http\Controllers\TicketStatusController;
use App\Domains\Ticketing\Http\Controllers\TicketWatcherController;
use App\Domains\Workspace\Http\Controllers\AgentTaskController;
use App\Domains\Workspace\Http\Controllers\AgentTaskStateController;
use App\Domains\Workspace\Http\Controllers\QuickReplyController;
use App\Domains\Workspace\Http\Controllers\QuickReplyRenderController;
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
    Route::post('channels/email/inbound', [InboundEmailWebhookController::class, 'store'])->middleware(['public.protect', 'idempotency']);

    Route::post('channels/whatsapp/inbound', [ProviderInboundWebhookController::class, 'handleWhatsapp'])->middleware(['idempotency']);
    Route::post('channels/whatsapp/receipts', [ProviderDeliveryReceiptController::class, 'handleWhatsapp']);
    Route::post('channels/sms/inbound', [ProviderInboundWebhookController::class, 'handleSms'])->middleware(['idempotency']);
    Route::post('channels/sms/receipts', [ProviderDeliveryReceiptController::class, 'handleSms']);

    Route::get('channels/web-forms/{formKey}', [PublicWebFormController::class, 'show']);
    Route::post('channels/web-forms/{formKey}/submissions', [PublicWebFormController::class, 'store'])
        ->middleware(['public.protect', 'throttle:web-form', 'idempotency']);
    Route::get('channels/web-forms/submissions/{trackingToken}', [PublicWebFormController::class, 'status']);

    Route::post('channels/chat/sessions', [PublicChatSessionController::class, 'store'])
        ->middleware(['public.protect', 'throttle:chat', 'idempotency']);

    Route::get('public/knowledge/categories', [PublicKnowledgeArticleController::class, 'categories']);
    Route::get('public/knowledge/articles', [PublicKnowledgeArticleController::class, 'index']);
    Route::get('public/knowledge/articles/search', [PublicKnowledgeArticleController::class, 'search']);
    Route::get('public/knowledge/articles/{article}', [PublicKnowledgeArticleController::class, 'show']);
    Route::post('public/knowledge/articles/{article}/feedback', [KnowledgeArticleFeedbackController::class, 'store']);
});

Route::prefix('v1')->group(function () {
    Route::get('health/live', [HealthController::class, 'live']);
    Route::get('health/ready', [HealthController::class, 'ready']);
});

Route::prefix('v1/portal')->middleware(['throttle:portal'])->group(function () {
    Route::post('auth/register', [PortalAuthController::class, 'register'])
        ->middleware(['public.protect', 'bot.protect', 'throttle:portal-auth']);
    Route::post('auth/verify', [PortalAuthController::class, 'verify'])
        ->middleware(['public.protect', 'throttle:portal-auth']);
    Route::post('auth/login', [PortalAuthController::class, 'login'])
        ->middleware(['public.protect', 'bot.protect', 'throttle:portal-auth']);

    Route::get('guest/tickets/{token}', [GuestTicketTrackingController::class, 'show'])
        ->middleware(['public.protect']);
    Route::post('guest/feedback/{token}', [TicketFeedbackController::class, 'storeByInvitation'])
        ->middleware(['public.protect', 'idempotency']);

    Route::middleware(['auth:portal', 'portal.auth'])->group(function () {
        Route::post('auth/logout', [PortalAuthController::class, 'logout']);
        Route::get('me', [PortalAccountController::class, 'show']);
        Route::get('tickets', [PortalTicketController::class, 'index']);
        Route::post('tickets', [PortalTicketController::class, 'store'])->middleware('idempotency');
        Route::get('tickets/{ticket}', [PortalTicketController::class, 'show']);
        Route::get('tickets/{ticket}/messages', [PortalTicketMessageController::class, 'index']);
        Route::post('tickets/{ticket}/messages', [PortalTicketMessageController::class, 'store'])
            ->middleware('idempotency');
        Route::post('tickets/{ticket}/feedback', [TicketFeedbackController::class, 'store'])
            ->middleware('idempotency');
        Route::get('attachments/{attachment}', [PortalAttachmentController::class, 'show']);
    });
});

Route::middleware(['auth:sanctum', 'portal.deny'])->prefix('v1')->group(function () {
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

    Route::get('tickets/{ticket}/watchers', [TicketWatcherController::class, 'index']);
    Route::post('tickets/{ticket}/watchers', [TicketWatcherController::class, 'store'])->middleware('idempotency');
    Route::delete('tickets/{ticket}/watchers/{user}', [TicketWatcherController::class, 'destroy']);

    Route::get('ticket-categories', [TicketCategoryController::class, 'index']);
    Route::post('ticket-categories', [TicketCategoryController::class, 'store'])->middleware('idempotency');
    Route::patch('ticket-categories/{category}', [TicketCategoryController::class, 'update'])->middleware('idempotency');
    Route::delete('ticket-categories/{category}', [TicketCategoryController::class, 'destroy']);

    Route::get('agent-tasks', [AgentTaskController::class, 'index']);
    Route::post('agent-tasks', [AgentTaskController::class, 'store'])->middleware('idempotency');
    Route::get('agent-tasks/{task}', [AgentTaskController::class, 'show']);
    Route::patch('agent-tasks/{task}', [AgentTaskController::class, 'update'])->middleware('idempotency');
    Route::delete('agent-tasks/{task}', [AgentTaskController::class, 'destroy']);
    Route::post('agent-tasks/{task}/state', [AgentTaskStateController::class, 'store'])->middleware('idempotency');

    Route::get('channels/email/inbound', [InboundEmailReplayController::class, 'index']);
    Route::post('channels/email/inbound/{record}/replay', [InboundEmailReplayController::class, 'replay'])->middleware('idempotency');

    Route::apiResource('channels/web-forms', WebFormController::class);

    Route::get('messaging/templates', [ProviderMessageTemplateController::class, 'index']);

    Route::get('quick-replies', [QuickReplyController::class, 'index']);
    Route::post('quick-replies', [QuickReplyController::class, 'store'])->middleware('idempotency');
    Route::get('quick-replies/{reply}', [QuickReplyController::class, 'show']);
    Route::patch('quick-replies/{reply}', [QuickReplyController::class, 'update'])->middleware('idempotency');
    Route::delete('quick-replies/{reply}', [QuickReplyController::class, 'destroy']);
    Route::post('quick-replies/render', [QuickReplyRenderController::class, 'store']);

    Route::get('knowledge/categories', [KnowledgeCategoryController::class, 'index']);
    Route::post('knowledge/categories', [KnowledgeCategoryController::class, 'store'])->middleware('idempotency');
    Route::patch('knowledge/categories/{category}', [KnowledgeCategoryController::class, 'update'])->middleware('idempotency');

    Route::get('knowledge/articles', [KnowledgeArticleController::class, 'index']);
    Route::post('knowledge/articles', [KnowledgeArticleController::class, 'store'])->middleware('idempotency');
    Route::get('knowledge/articles/search', [KnowledgeArticleSearchController::class, 'index']);
    Route::get('knowledge/articles/{article}', [KnowledgeArticleController::class, 'show']);
    Route::patch('knowledge/articles/{article}', [KnowledgeArticleController::class, 'update'])->middleware('idempotency');
    Route::post('knowledge/articles/{article}/state', [KnowledgeArticleStateController::class, 'store'])->middleware('idempotency');
    Route::get('knowledge/articles/{article}/versions', [KnowledgeArticleVersionController::class, 'index']);
    Route::post('knowledge/articles/{article}/versions/{version}/restore', [KnowledgeArticleVersionController::class, 'store'])->middleware('idempotency');
    Route::post('knowledge/articles/render', [KnowledgeArticleRenderController::class, 'store']);

    // SLA management
    Route::apiResource('sla/policies', SlaPolicyController::class);
    Route::get('tickets/{ticket}/sla/{clock}', [TicketSlaController::class, 'show']);
    Route::post('tickets/{ticket}/sla/{clock}/reset', [TicketSlaController::class, 'reset'])->middleware('idempotency');

    // Automation rules management
    Route::post('automation/rules', [AutomationRuleController::class, 'store'])->middleware('idempotency');
    Route::get('automation/rules', [AutomationRuleController::class, 'index']);
    Route::get('automation/rules/{rule}', [AutomationRuleController::class, 'show']);
    Route::patch('automation/rules/{rule}', [AutomationRuleController::class, 'update'])->middleware('idempotency');
    Route::delete('automation/rules/{rule}', [AutomationRuleController::class, 'destroy']);
    Route::get('automation/executions', [AutomationRuleExecutionController::class, 'index']);
    Route::post('tickets/{ticket}/escalate', [TicketEscalationController::class, 'store'])->middleware('idempotency');

    // AI assistance
    Route::post('tickets/{ticket}/ai/summary', [TicketAiAssistController::class, 'summary'])->middleware('idempotency');
    Route::post('tickets/{ticket}/ai/suggested-reply', [TicketAiAssistController::class, 'suggestedReply'])->middleware('idempotency');
    Route::post('tickets/{ticket}/ai/classify', [TicketAiAssistController::class, 'classify'])->middleware('idempotency');
    Route::post('tickets/{ticket}/ai/suggested-articles', [TicketAiAssistController::class, 'suggestedArticles'])->middleware('idempotency');
    Route::get('ai/suggestions', [AiSuggestionController::class, 'index']);
    Route::post('ai/suggestions/{suggestion}/resolve/{decision}', [AiSuggestionController::class, 'resolve'])->middleware('idempotency');
    Route::get('ai/usage', [AiUsageController::class, 'index']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('notifications/preferences', [NotificationPreferenceController::class, 'index']);
    Route::put('notifications/preferences', [NotificationPreferenceController::class, 'update']);
    Route::get('notifications/deliveries/failed', [NotificationDeliveryController::class, 'index']);

    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/{report}', [ReportController::class, 'show']);
    Route::post('reports/{report}/export', [ReportExportController::class, 'store'])->middleware('idempotency');

    // Report scheduling
    Route::apiResource('report-schedules', ReportScheduleController::class);

    // Webhooks (machine-to-machine)
    Route::apiResource('webhooks/subscriptions', WebhookSubscriptionController::class);
    Route::get('webhooks/deliveries', [WebhookDeliveryController::class, 'index']);
    Route::get('customers/{customer}/erp-context', [CustomerErpContextController::class, 'show']);
    Route::apiResource('import-runs', ImportRunController::class)->only(['index', 'show', 'store']);

    // API tokens (staff management)
    Route::apiResource('integration/tokens', ApiTokenController::class)->only(['index', 'store', 'destroy']);
});
