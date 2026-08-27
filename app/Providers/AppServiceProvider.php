<?php

namespace App\Providers;

use App\Domains\Ai\Services\Provider\AiProvider;
use App\Domains\Ai\Services\Provider\NullAiProvider;
use App\Domains\Ai\Services\Retention\AiSuggestionPurgeHandler;
use App\Domains\Ai\Services\Retention\AiUsagePurgeHandler;
use App\Domains\Channels\Chat\Services\Retention\ChatSessionPurgeHandler;
use App\Domains\Channels\Email\Services\Retention\InboundEmailPurgeHandler;
use App\Domains\Channels\Messaging\Jobs\SendProviderMessageJob;
use App\Domains\Channels\Messaging\Services\Retention\ProviderInboundPurgeHandler;
use App\Domains\Channels\Messaging\Services\Transport\NullProviderMessageTransport;
use App\Domains\Channels\Messaging\Services\Transport\ProviderMessageTransport;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Policies\WebFormPolicy;
use App\Domains\Channels\WebForm\Services\Retention\WebFormSubmissionPurgeHandler;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Policies\CustomerContactPolicy;
use App\Domains\Customers\Policies\CustomerDuplicatePolicy;
use App\Domains\Customers\Policies\CustomerNotePolicy;
use App\Domains\Customers\Policies\CustomerPolicy;
use App\Domains\Customers\Services\ArabicTextNormaliser;
use App\Domains\Customers\Services\Merge\MergeRelationRegistry;
use App\Domains\Customers\Services\Merge\Relations\AttachmentMergeRelation;
use App\Domains\Customers\Services\Merge\Relations\ContactMergeRelation;
use App\Domains\Customers\Services\Merge\Relations\EventMergeRelation;
use App\Domains\Customers\Services\Merge\Relations\NoteMergeRelation;
use App\Domains\Customers\Services\Retention\CustomerNotePurgeHandler;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Customers\Services\Timeline\Sources\CustomerEventSource;
use App\Domains\Customers\Services\Timeline\Sources\NoteTimelineSource;
use App\Domains\Customers\Services\Timeline\TimelineRegistry;
use App\Domains\Integrations\Services\Erp\HttpErpClient;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Knowledge\Policies\KnowledgeArticlePolicy;
use App\Domains\Knowledge\Policies\KnowledgeCategoryPolicy;
use App\Domains\Knowledge\Services\ArticleReplyRenderer;
use App\Domains\Knowledge\Services\KnowledgeCategoryTree;
use App\Domains\Knowledge\Services\Lifecycle\ArticleTransitionMap;
use App\Domains\Knowledge\Services\Retention\ArticleFeedbackPurgeHandler;
use App\Domains\Knowledge\Services\Search\ArticleSearchIndexer;
use App\Domains\Knowledge\Services\Visibility\ArticleAudienceResolver;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;
use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Events\NotifiableEvent;
use App\Domains\Notifications\Listeners\DispatchNotificationsListener;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Policies\NotificationPolicy;
use App\Domains\Notifications\Policies\NotificationPreferencePolicy;
use App\Domains\Notifications\Services\Channels\InAppChannel;
use App\Domains\Notifications\Services\Channels\MailChannel;
use App\Domains\Notifications\Services\Channels\NotificationChannelRegistry;
use App\Domains\Notifications\Services\NotificationDispatcher;
use App\Domains\Notifications\Services\NotificationPayloadAuthoriser;
use App\Domains\Notifications\Services\NotificationPreferenceResolver;
use App\Domains\Notifications\Services\Retention\NotificationPurgeHandler;
use App\Domains\Notifications\Services\TemplateRenderer;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Models\Team;
use App\Domains\Organisation\Policies\BranchPolicy;
use App\Domains\Organisation\Policies\DepartmentPolicy;
use App\Domains\Organisation\Policies\TeamPolicy;
use App\Domains\Organisation\Services\BranchUsageChecker;
use App\Domains\Organisation\Services\DefaultBranchUsageChecker;
use App\Domains\Organisation\Services\DepartmentUsageChecker;
use App\Domains\Organisation\Services\NullDepartmentUsageChecker;
use App\Domains\Organisation\Services\WorkingTimeService;
use App\Domains\Portal\Services\Retention\PortalTokenPurgeHandler;
use App\Domains\Reporting\Services\Definitions\AgentPerformanceReport;
use App\Domains\Reporting\Services\Definitions\BacklogAgingReport;
use App\Domains\Reporting\Services\Definitions\ManagementDashboardReport;
use App\Domains\Reporting\Services\Definitions\ReportRegistry;
use App\Domains\Reporting\Services\Definitions\SatisfactionReport;
use App\Domains\Reporting\Services\Definitions\SlaPerformanceReport;
use App\Domains\Reporting\Services\Definitions\TicketVolumeReport;
use App\Domains\Reporting\Services\Retention\ReportExportPurgeHandler;
use App\Domains\Reporting\Services\Scoping\ReportScopeResolver;
use App\Domains\Security\Models\AuditLog;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Policies\AuditLogPolicy;
use App\Domains\Security\Policies\RolePolicy;
use App\Domains\Security\Policies\UserPolicy;
use App\Domains\Sla\Services\SlaClockHooksBridge;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Sla\Services\SlaEvaluator;
use App\Domains\Sla\Services\SlaPolicyResolver;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Policies\TicketCategoryPolicy;
use App\Domains\Ticketing\Policies\TicketMessagePolicy;
use App\Domains\Ticketing\Policies\TicketPolicy;
use App\Domains\Ticketing\Services\Merge\Relations\LinkMergeRelation;
use App\Domains\Ticketing\Services\Merge\Relations\MessageMergeRelation;
use App\Domains\Ticketing\Services\Merge\Relations\TagMergeRelation;
use App\Domains\Ticketing\Services\Merge\TicketMergeRelationRegistry;
use App\Domains\Ticketing\Services\Retention\TicketMessagePurgeHandler;
use App\Domains\Ticketing\Services\Routing\DepartmentTransferEvaluator;
use App\Domains\Ticketing\Services\Sla\NullSlaClockHooks;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use App\Domains\Workspace\Services\Retention\AgentTaskPurgeHandler;
use App\Models\User;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\Policies\AttachmentPolicy;
use App\Support\Attachments\Scanning\MalwareScanner;
use App\Support\Attachments\Scanning\NullMalwareScanner;
use App\Support\Http\BotProtection\BotProtectionGuard;
use App\Support\Http\BotProtection\NullBotProtectionGuard;
use App\Support\Http\RequestId;
use App\Support\I18n\LocaleResolver;
use App\Support\I18n\LocalizationSettings;
use App\Support\Retention\Handlers\AttachmentPurgeHandler;
use App\Support\Retention\Handlers\AuditPurgeHandler;
use App\Support\Retention\Handlers\NullPurgeHandler;
use App\Support\Retention\RetentionRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DepartmentUsageChecker::class,
            NullDepartmentUsageChecker::class
        );

        $this->app->bind(
            BranchUsageChecker::class,
            DefaultBranchUsageChecker::class
        );

        $this->app->bind(
            DepartmentTransferEvaluator::class,
            AutomationBackedTransferEvaluator::class
        );

        $driver = config('security.scanning.driver');
        $this->app->bind(
            MalwareScanner::class,
            $driver === 'null' ? NullMalwareScanner::class : NullMalwareScanner::class
        );

        $botDriver = config('security.public_endpoints.bot_protection.driver');
        $this->app->bind(
            BotProtectionGuard::class,
            $botDriver === 'null' ? NullBotProtectionGuard::class : NullBotProtectionGuard::class
        );

        $aiProvider = config('ai.provider');
        $this->app->bind(
            AiProvider::class,
            $aiProvider === 'null' ? NullAiProvider::class : NullAiProvider::class
        );

        $erpEnabled = config('integrations.erp.enabled', false);
        $this->app->bind(
            ErpClient::class,
            $erpEnabled ? HttpErpClient::class : NullErpClient::class
        );

        $this->app->singleton(WorkingTimeService::class);
        $this->app->singleton(RequestId::class);
        $this->app->singleton(LocalizationSettings::class);
        $this->app->singleton(LocaleResolver::class);

        $this->app->bind(TextNormaliser::class, ArabicTextNormaliser::class);

        $this->app->singleton(TimelineRegistry::class, function ($app) {
            $registry = new TimelineRegistry;
            $registry->register($app->make(CustomerEventSource::class));
            $registry->register($app->make(NoteTimelineSource::class));
            // Ticket and channel-message sources register here once BE-Ticketing lands.

            return $registry;
        });

        $this->app->singleton(TicketTransitionMap::class);
        $this->app->singleton(ReopenWindow::class);

        $this->app->singleton(SlaPolicyResolver::class);
        $this->app->singleton(SlaClockService::class);
        $this->app->singleton(SlaEvaluator::class);
        $this->app->singleton(SlaClockHooksBridge::class);

        $this->app->bind(SlaClockHooks::class, NullSlaClockHooks::class);

        $this->app->bind(TicketAutomationHooks::class, TicketAutomationBridge::class);

        $this->app->singleton(ConditionEvaluator::class);
        $this->app->singleton(TicketFactProvider::class);
        $this->app->singleton(RuleEngine::class);

        $this->app->singleton(RuleActionRegistry::class, function ($app) {
            $registry = new RuleActionRegistry;
            $registry->register($app->make(AssignAction::class));
            $registry->register($app->make(ReassignAction::class));
            $registry->register($app->make(TransferDepartmentAction::class));
            $registry->register($app->make(RaisePriorityAction::class));
            $registry->register($app->make(ChangeStatusAction::class));
            $registry->register($app->make(AddTagAction::class));
            $registry->register($app->make(NotifyAction::class));
            $registry->register($app->make(EscalateAction::class));

            return $registry;
        });

        $this->app->singleton(ReportRegistry::class, function ($app) {
            $registry = new ReportRegistry;
            $registry->register($app->make(TicketVolumeReport::class));
            $registry->register($app->make(SlaPerformanceReport::class));
            $registry->register($app->make(AgentPerformanceReport::class));
            $registry->register($app->make(SatisfactionReport::class));
            $registry->register($app->make(BacklogAgingReport::class));
            $registry->register($app->make(ManagementDashboardReport::class));

            return $registry;
        });

        $this->app->singleton(ReportScopeResolver::class);

        $this->app->singleton(ReportExporterRegistry::class, function ($app) {
            $registry = new ReportExporterRegistry;
            $registry->register($app->make(CsvReportExporter::class));
            $registry->register($app->make(XlsxReportExporter::class));
            $registry->register($app->make(PdfReportExporter::class));

            return $registry;
        });

        $this->app->singleton('automation.strategies', function ($app) {
            return [
                'manual' => $app->make(ManualStrategy::class),
                'round_robin' => $app->make(RoundRobinStrategy::class),
                'least_busy' => $app->make(LeastBusyStrategy::class),
                'skill_based' => $app->make(SkillBasedStrategy::class),
            ];
        });

        $this->app->singleton(TicketMergeRelationRegistry::class, function ($app) {
            $registry = new TicketMergeRelationRegistry;
            $registry->register($app->make(TagMergeRelation::class));
            $registry->register($app->make(LinkMergeRelation::class));
            $registry->register($app->make(MessageMergeRelation::class));

            return $registry;
        });

        $this->app->singleton(MergeRelationRegistry::class, function ($app) {
            $registry = new MergeRelationRegistry;
            $registry->register($app->make(NoteMergeRelation::class));
            $registry->register($app->make(EventMergeRelation::class));
            $registry->register($app->make(ContactMergeRelation::class));
            $registry->register($app->make(AttachmentMergeRelation::class));
            // Ticket and message relations register here once BE-Ticketing and messaging land.

            return $registry;
        });

        $this->app->singleton(RetentionRegistry::class, function ($app) {
            $registry = new RetentionRegistry;
            $registry->register($app->make(AttachmentPurgeHandler::class));
            $registry->register($app->make(AuditPurgeHandler::class));
            $registry->register($app->make(CustomerNotePurgeHandler::class));
            $registry->register($app->make(TicketMessagePurgeHandler::class));
            $registry->register($app->make(NotificationPurgeHandler::class));
            $registry->register($app->make(AgentTaskPurgeHandler::class));
            $registry->register($app->make(InboundEmailPurgeHandler::class));
            $registry->register($app->make(WebFormSubmissionPurgeHandler::class));
            $registry->register($app->make(ProviderInboundPurgeHandler::class));
            $registry->register($app->make(ChatSessionPurgeHandler::class));
            $registry->register($app->make(ArticleFeedbackPurgeHandler::class));
            $registry->register($app->make(AiSuggestionPurgeHandler::class));
            $registry->register($app->make(AiUsagePurgeHandler::class));
            $registry->register(new PortalTokenPurgeHandler);
            $registry->register($app->make(ReportExportPurgeHandler::class));
            $registry->register($app->make(WebhookDeliveryPurgeHandler::class));
            $registry->register($app->make(ImportRunPurgeHandler::class));
            $registry->register(new NullPurgeHandler('tickets'));
            $registry->register(new NullPurgeHandler('logs'));

            return $registry;
        });

        $this->app->singleton(NotificationChannelRegistry::class, function ($app) {
            $registry = new NotificationChannelRegistry;
            $registry->register(NotificationChannel::InApp, $app->make(InAppChannel::class));
            $registry->register(NotificationChannel::Mail, $app->make(MailChannel::class));

            return $registry;
        });

        $this->app->singleton(NotificationPreferenceResolver::class);
        $this->app->singleton(TemplateRenderer::class);
        $this->app->singleton(NotificationPayloadAuthoriser::class);
        $this->app->singleton(NotificationDispatcher::class);

        $this->app->singleton(QuickReplyRenderer::class);
        $this->app->singleton(ArticleReplyRenderer::class);
        $this->app->singleton(ArticleTransitionMap::class);
        $this->app->singleton(ArticleQueryScope::class);
        $this->app->singleton(ArticleAudienceResolver::class);
        $this->app->singleton(ArticleSearchIndexer::class);
        $this->app->singleton(KnowledgeCategoryTree::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerContact::class, CustomerContactPolicy::class);
        Gate::policy(CustomerDuplicateCandidate::class, CustomerDuplicatePolicy::class);
        Gate::policy(CustomerNote::class, CustomerNotePolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(TicketCategory::class, TicketCategoryPolicy::class);
        Gate::policy(TicketMessage::class, TicketMessagePolicy::class);
        Gate::policy(TicketStatusDefinition::class, TicketStatusDefinitionPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(NotificationPreference::class, NotificationPreferencePolicy::class);
        Gate::policy(AgentTask::class, AgentTaskPolicy::class);
        Gate::policy(QuickReply::class, QuickReplyPolicy::class);
        Gate::policy(WebForm::class, WebFormPolicy::class);
        Gate::policy(ProviderMessageTemplate::class, ProviderMessageTemplatePolicy::class);
        Gate::policy(KnowledgeArticle::class, KnowledgeArticlePolicy::class);
        Gate::policy(KnowledgeCategory::class, KnowledgeCategoryPolicy::class);
        Gate::policy(ApiToken::class, ApiTokenPolicy::class);

        $this->app->bind(InboundMailTransport::class, WebhookInboundMailTransport::class);

        $whatsappTransport = config('channels.whatsapp.transport', 'null');
        $smsTransport = config('channels.sms.transport', 'null');

        $this->app->when(SendProviderMessageJob::class)
            ->needs(ProviderMessageTransport::class)
            ->give(function () use ($whatsappTransport) {
                $transport = $whatsappTransport === 'null' ? 'null' : $whatsappTransport;

                return match ($transport) {
                    'null' => $this->app->make(NullProviderMessageTransport::class),
                    default => $this->app->make(NullProviderMessageTransport::class),
                };
            });

        foreach (PermissionKey::all() as $key) {
            Gate::define($key, fn (User $user) => in_array($key, $user->permissionKeys(), true));
        }

        Gate::before(function (User $user, string $ability) {
            if (in_array($ability, PermissionKey::all(), true)
                && $user->roles()->where('name', Role::ADMINISTRATOR)->exists()) {
                return true;
            }

            return null;
        });

        RateLimiter::for('api', function ($request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(120)->by((string) $key);
        });

        RateLimiter::for('api-writes', function ($request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by((string) $key);
        });

        RateLimiter::for('public', function ($request) {
            $throttleConfig = config('security.public_endpoints.throttle');
            [$limit, $window] = explode(',', $throttleConfig);

            return Limit::perMinute((int) $limit)->by($request->ip());
        });

        RateLimiter::for('uploads', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        RateLimiter::for('web-form', function ($request) {
            $maxPerMinute = (int) config('channels.web_form.rate_limit.max_per_minute', 5);
            $maxPerHour = (int) config('channels.web_form.rate_limit.max_per_hour', 30);
            $formKey = $request->route('formKey') ?? '';
            $key = hash('sha256', ($request->ip() ?? '').$formKey);

            return [
                Limit::perMinute($maxPerMinute)->by($key),
                Limit::perHour($maxPerHour)->by($key),
            ];
        });

        RateLimiter::for('chat', function ($request) {
            $maxPerMinute = (int) config('channels.chat.rate_limit.max_per_minute', 30);
            $maxPerHour = (int) config('channels.chat.rate_limit.max_per_hour', 300);
            $key = hash('sha256', ($request->ip() ?? ''));

            return [
                Limit::perMinute($maxPerMinute)->by($key),
                Limit::perHour($maxPerHour)->by($key),
            ];
        });

        RateLimiter::for('portal', function ($request) {
            return Limit::perMinute(120)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        RateLimiter::for('portal-auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        Queue::createPayloadUsing(function () {
            $currentId = RequestId::current();

            return ! empty($currentId) ? ['request_id' => $currentId] : [];
        });

        Queue::before(function ($event) {
            $requestId = $event->job->payload()['request_id'] ?? null;
            if ($requestId) {
                RequestId::set($requestId);
            }
        });

        Queue::after(function ($event) {
            RequestId::set('');
        });

        Queue::failing(function ($event) {
            RequestId::set('');
        });

        Event::listen(
            NotifiableEvent::class,
            DispatchNotificationsListener::class,
        );
    }

    private function slaTablesExist(): bool
    {
        static $exists;

        if ($exists === null) {
            try {
                $exists = Schema::hasTable('sla_policies');
            } catch (\Throwable) {
                $exists = false;
            }
        }

        return $exists;
    }
}
