<?php

namespace Database\Seeders;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsAndRolesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdministrator();
        $this->seedManager();
        $this->seedSupervisor();
        $this->seedAgent();
        $this->seedViewer();
    }

    private function seedAdministrator(): void
    {
        $role = Role::firstOrCreate(
            ['name' => Role::ADMINISTRATOR],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مدير النظام',
                    'en' => 'Administrator',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, PermissionKey::all());
    }

    private function seedManager(): void
    {
        $permissions = [
            // admin.*
            PermissionKey::ADMIN_ROLES_MANAGE,
            PermissionKey::ADMIN_USERS_MANAGE,
            PermissionKey::ADMIN_USERS_INVITE,
            PermissionKey::ADMIN_USERS_ACTIVATE,
            PermissionKey::ADMIN_USERS_DEACTIVATE,
            PermissionKey::ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY,
            PermissionKey::ADMIN_STRUCTURE_MANAGE,
            // org.*
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_BRANCHES_MANAGE_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_MANAGE_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_MANAGE_ANY,
            // tickets.*
            PermissionKey::TICKETS_VIEW_ANY,
            PermissionKey::TICKETS_CREATE,
            PermissionKey::TICKETS_UPDATE,
            PermissionKey::TICKETS_ASSIGN,
            PermissionKey::TICKETS_TRANSFER_AGENT,
            PermissionKey::TICKETS_TRANSFER_DEPARTMENT,
            PermissionKey::TICKETS_RECLASSIFY,
            PermissionKey::TICKETS_TAG,
            PermissionKey::TICKETS_HISTORY_VIEW,
            PermissionKey::TICKETS_CATEGORIES_MANAGE,
            PermissionKey::TICKETS_STATUS_CHANGE,
            PermissionKey::TICKETS_REOPEN,
            PermissionKey::TICKETS_SPAM_MARK,
            PermissionKey::TICKETS_SPAM_RESTORE,
            PermissionKey::TICKETS_MERGE,
            PermissionKey::TICKETS_SPLIT,
            PermissionKey::TICKETS_LINK,
            PermissionKey::TICKETS_STATUSES_MANAGE,
            // ticket messages.*
            PermissionKey::TICKET_MESSAGE_VIEW,
            PermissionKey::TICKET_MESSAGE_SEND,
            PermissionKey::TICKET_MESSAGE_INTERNAL_VIEW,
            PermissionKey::TICKET_MESSAGE_INTERNAL_WRITE,
            PermissionKey::TICKET_MESSAGE_RETRY,
            // customers.*
            PermissionKey::CUSTOMERS_VIEW,
            PermissionKey::CUSTOMERS_CREATE,
            PermissionKey::CUSTOMERS_UPDATE,
            PermissionKey::CUSTOMERS_BLOCK,
            PermissionKey::CUSTOMERS_CONTACT_MANAGE,
            PermissionKey::CUSTOMERS_NOTE_VIEW,
            PermissionKey::CUSTOMERS_NOTE_CREATE,
            PermissionKey::CUSTOMERS_NOTE_DELETE,
            PermissionKey::CUSTOMERS_ATTACHMENT_MANAGE,
            PermissionKey::CUSTOMERS_TIMELINE_VIEW,
            PermissionKey::CUSTOMERS_DUPLICATE_VIEW,
            PermissionKey::CUSTOMERS_DUPLICATE_REVIEW,
            PermissionKey::CUSTOMERS_MERGE,
            // sla.*
            PermissionKey::SLA_POLICIES_VIEW,
            PermissionKey::SLA_POLICIES_MANAGE,
            PermissionKey::SLA_RESET,
            // automation.*
            PermissionKey::AUTOMATION_RULES_VIEW,
            PermissionKey::AUTOMATION_RULES_MANAGE,
            PermissionKey::AUTOMATION_EXECUTIONS_VIEW,
            PermissionKey::TICKETS_ESCALATE,
            // workspace.*
            PermissionKey::WORKSPACE_TASKS_CREATE,
            PermissionKey::WORKSPACE_TASKS_VIEW_OWN,
            PermissionKey::WORKSPACE_TASKS_VIEW_OTHERS,
            PermissionKey::WORKSPACE_TASKS_REASSIGN,
            PermissionKey::WORKSPACE_QUICK_REPLIES_MANAGE_SHARED,
            PermissionKey::WORKSPACE_TICKET_WATCHERS_VIEW,
            PermissionKey::WORKSPACE_TICKET_MESSAGE_MENTION,
            // channels.*
            PermissionKey::CHANNELS_EMAIL_REPLAY_LIST,
            PermissionKey::CHANNELS_EMAIL_REPLAY_ACTION,
            PermissionKey::CHANNELS_WEB_FORM_VIEW,
            PermissionKey::CHANNELS_WEB_FORM_CREATE,
            PermissionKey::CHANNELS_WEB_FORM_UPDATE,
            PermissionKey::CHANNELS_WEB_FORM_DELETE,
            PermissionKey::CHANNELS_MESSAGING_TEMPLATES_VIEW,
            PermissionKey::CHANNELS_MESSAGING_TEMPLATES_MANAGE,
            // knowledge.*
            PermissionKey::KNOWLEDGE_ARTICLES_VIEW,
            PermissionKey::KNOWLEDGE_ARTICLES_CREATE,
            PermissionKey::KNOWLEDGE_ARTICLES_UPDATE,
            PermissionKey::KNOWLEDGE_ARTICLES_PUBLISH,
            PermissionKey::KNOWLEDGE_ARTICLES_ARCHIVE,
            PermissionKey::KNOWLEDGE_ARTICLES_VERSIONS_RESTORE,
            PermissionKey::KNOWLEDGE_CATEGORIES_MANAGE,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::MANAGER],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مدير',
                    'en' => 'Manager',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedSupervisor(): void
    {
        $permissions = [
            // org.*.view.any
            PermissionKey::ORG_BRANCHES_VIEW_ANY,
            PermissionKey::ORG_DEPARTMENTS_VIEW_ANY,
            PermissionKey::ORG_TEAMS_VIEW_ANY,
            // tickets.*
            PermissionKey::TICKETS_VIEW_DEPARTMENT,
            PermissionKey::TICKETS_VIEW_TEAM,
            PermissionKey::TICKETS_CREATE,
            PermissionKey::TICKETS_UPDATE,
            PermissionKey::TICKETS_ASSIGN,
            PermissionKey::TICKETS_RECLASSIFY,
            PermissionKey::TICKETS_TAG,
            PermissionKey::TICKETS_HISTORY_VIEW,
            PermissionKey::TICKETS_STATUS_CHANGE,
            PermissionKey::TICKETS_REOPEN,
            PermissionKey::TICKETS_SPAM_MARK,
            PermissionKey::TICKETS_MERGE,
            PermissionKey::TICKETS_SPLIT,
            PermissionKey::TICKETS_LINK,
            // ticket messages.*
            PermissionKey::TICKET_MESSAGE_VIEW,
            PermissionKey::TICKET_MESSAGE_SEND,
            PermissionKey::TICKET_MESSAGE_INTERNAL_VIEW,
            PermissionKey::TICKET_MESSAGE_INTERNAL_WRITE,
            PermissionKey::TICKET_MESSAGE_RETRY,
            // customers.*
            PermissionKey::CUSTOMERS_VIEW,
            PermissionKey::CUSTOMERS_CREATE,
            PermissionKey::CUSTOMERS_UPDATE,
            PermissionKey::CUSTOMERS_BLOCK,
            PermissionKey::CUSTOMERS_CONTACT_MANAGE,
            PermissionKey::CUSTOMERS_NOTE_VIEW,
            PermissionKey::CUSTOMERS_NOTE_CREATE,
            PermissionKey::CUSTOMERS_NOTE_DELETE,
            PermissionKey::CUSTOMERS_ATTACHMENT_MANAGE,
            PermissionKey::CUSTOMERS_TIMELINE_VIEW,
            PermissionKey::CUSTOMERS_DUPLICATE_VIEW,
            PermissionKey::CUSTOMERS_DUPLICATE_REVIEW,
            // sla.*
            PermissionKey::SLA_POLICIES_VIEW,
            PermissionKey::SLA_RESET,
            // automation.*
            PermissionKey::AUTOMATION_RULES_VIEW,
            PermissionKey::AUTOMATION_EXECUTIONS_VIEW,
            PermissionKey::TICKETS_ESCALATE,
            // workspace.*
            PermissionKey::WORKSPACE_TASKS_CREATE,
            PermissionKey::WORKSPACE_TASKS_VIEW_OWN,
            PermissionKey::WORKSPACE_TASKS_VIEW_OTHERS,
            PermissionKey::WORKSPACE_TASKS_REASSIGN,
            PermissionKey::WORKSPACE_QUICK_REPLIES_MANAGE_SHARED,
            PermissionKey::WORKSPACE_TICKET_WATCHERS_VIEW,
            PermissionKey::WORKSPACE_TICKET_MESSAGE_MENTION,
            // knowledge.*
            PermissionKey::KNOWLEDGE_ARTICLES_VIEW,
            PermissionKey::KNOWLEDGE_ARTICLES_CREATE,
            PermissionKey::KNOWLEDGE_ARTICLES_UPDATE,
            PermissionKey::KNOWLEDGE_ARTICLES_PUBLISH,
            PermissionKey::KNOWLEDGE_ARTICLES_ARCHIVE,
            PermissionKey::KNOWLEDGE_ARTICLES_VERSIONS_RESTORE,
            PermissionKey::KNOWLEDGE_CATEGORIES_MANAGE,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::SUPERVISOR],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مشرف',
                    'en' => 'Supervisor',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedAgent(): void
    {
        $permissions = [
            PermissionKey::TICKETS_VIEW_TEAM,
            PermissionKey::TICKETS_VIEW_OWN,
            PermissionKey::TICKETS_CREATE,
            PermissionKey::TICKETS_UPDATE,
            PermissionKey::TICKETS_ASSIGN,
            PermissionKey::TICKETS_CLAIM,
            PermissionKey::TICKETS_QUEUE_VIEW,
            PermissionKey::TICKETS_RECLASSIFY,
            PermissionKey::TICKETS_TAG,
            PermissionKey::TICKETS_HISTORY_VIEW,
            PermissionKey::TICKETS_STATUS_CHANGE,
            PermissionKey::TICKETS_REOPEN,
            PermissionKey::TICKETS_SPAM_MARK,
            PermissionKey::TICKETS_SPLIT,
            PermissionKey::TICKETS_LINK,
            // ticket messages.*
            PermissionKey::TICKET_MESSAGE_VIEW,
            PermissionKey::TICKET_MESSAGE_SEND,
            PermissionKey::TICKET_MESSAGE_INTERNAL_VIEW,
            PermissionKey::TICKET_MESSAGE_INTERNAL_WRITE,
            PermissionKey::TICKET_MESSAGE_RETRY,
            // customers.*
            PermissionKey::CUSTOMERS_VIEW,
            PermissionKey::CUSTOMERS_NOTE_VIEW,
            PermissionKey::CUSTOMERS_NOTE_CREATE,
            PermissionKey::CUSTOMERS_TIMELINE_VIEW,
            // sla.*
            PermissionKey::SLA_POLICIES_VIEW,
            // automation.*
            PermissionKey::TICKETS_ESCALATE,
            // workspace.*
            PermissionKey::WORKSPACE_TASKS_CREATE,
            PermissionKey::WORKSPACE_TASKS_VIEW_OWN,
            PermissionKey::WORKSPACE_TICKET_WATCHERS_VIEW,
            PermissionKey::WORKSPACE_TICKET_MESSAGE_MENTION,
            // knowledge.*
            PermissionKey::KNOWLEDGE_ARTICLES_VIEW,
            PermissionKey::KNOWLEDGE_ARTICLES_CREATE,
            PermissionKey::KNOWLEDGE_ARTICLES_UPDATE,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::AGENT],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'موظف دعم',
                    'en' => 'Support Agent',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function seedViewer(): void
    {
        $permissions = [
            PermissionKey::TICKETS_VIEW_OWN,
            // ticket messages.*
            PermissionKey::TICKET_MESSAGE_VIEW,
        ];

        $role = Role::firstOrCreate(
            ['name' => Role::VIEWER],
            [
                'uuid' => (string) Str::uuid(),
                'display_name' => [
                    'ar' => 'مطّلع',
                    'en' => 'Viewer',
                ],
                'is_system' => true,
            ]
        );

        $this->reconcilePermissions($role, $permissions);
    }

    private function reconcilePermissions(Role $role, array $desiredPermissions): void
    {
        if (! $role->is_system) {
            return;
        }

        $existingKeys = $role->permissions()->pluck('permission_key')->toArray();

        $keysToAdd = array_diff($desiredPermissions, $existingKeys);
        $keysToRemove = array_diff($existingKeys, $desiredPermissions);

        if (! empty($keysToAdd)) {
            $permissions = array_map(fn ($key) => ['permission_key' => $key], $keysToAdd);
            $role->permissions()->createMany($permissions);
        }

        if (! empty($keysToRemove)) {
            $role->permissions()
                ->whereIn('permission_key', $keysToRemove)
                ->delete();
        }
    }
}
