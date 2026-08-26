<?php

namespace App\Domains\Security\Permissions;

use ReflectionClass;

final class PermissionKey
{
    // ---- admin (module: admin) ------------------------------------------
    public const ADMIN_ROLES_MANAGE = 'admin.roles.manage';

    public const ADMIN_USERS_MANAGE = 'admin.users.manage';

    public const ADMIN_USERS_INVITE = 'admin.users.invite';

    public const ADMIN_USERS_ACTIVATE = 'admin.users.activate';

    public const ADMIN_USERS_DEACTIVATE = 'admin.users.deactivate';

    public const ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY = 'admin.users.manage_two_factor_policy';

    public const ADMIN_STRUCTURE_MANAGE = 'admin.structure.manage';

    public const ADMIN_AUDIT_VIEW = 'admin.audit.view';

    // ---- organisation (module: org) ------------------------------------
    public const ORG_BRANCHES_VIEW_ANY = 'org.branches.view.any';

    public const ORG_BRANCHES_MANAGE_ANY = 'org.branches.manage.any';

    public const ORG_DEPARTMENTS_VIEW_ANY = 'org.departments.view.any';

    public const ORG_DEPARTMENTS_MANAGE_ANY = 'org.departments.manage.any';

    public const ORG_TEAMS_VIEW_ANY = 'org.teams.view.any';

    public const ORG_TEAMS_MANAGE_ANY = 'org.teams.manage.any';

    // ---- tickets (module: tickets) - scope-aware -----------------------
    public const TICKETS_VIEW_OWN = 'tickets.view.own';

    public const TICKETS_VIEW_TEAM = 'tickets.view.team';

    public const TICKETS_VIEW_DEPARTMENT = 'tickets.view.department';

    public const TICKETS_VIEW_ANY = 'tickets.view.any';

    public const TICKETS_CREATE = 'tickets.create';

    public const TICKETS_UPDATE = 'tickets.update';

    public const TICKETS_ASSIGN = 'tickets.assign';

    public const TICKETS_CLAIM = 'tickets.claim';

    public const TICKETS_TRANSFER_AGENT = 'tickets.transfer.agent';

    public const TICKETS_TRANSFER_DEPARTMENT = 'tickets.transfer.department';

    public const TICKETS_QUEUE_VIEW = 'tickets.queue.view';

    public const TICKETS_RECLASSIFY = 'tickets.reclassify';

    public const TICKETS_TAG = 'tickets.tag';

    public const TICKETS_HISTORY_VIEW = 'tickets.history.view';

    public const TICKETS_CATEGORIES_MANAGE = 'tickets.categories.manage';

    public const TICKETS_STATUS_CHANGE = 'tickets.status.change';

    public const TICKETS_REOPEN = 'tickets.reopen';

    public const TICKETS_SPAM_MARK = 'tickets.spam.mark';

    public const TICKETS_SPAM_RESTORE = 'tickets.spam.restore';

    public const TICKETS_MERGE = 'tickets.merge';

    public const TICKETS_SPLIT = 'tickets.split';

    public const TICKETS_LINK = 'tickets.link';

    public const TICKET_MESSAGE_VIEW = 'ticket.message.view';

    public const TICKET_MESSAGE_SEND = 'ticket.message.send';

    public const TICKET_MESSAGE_INTERNAL_VIEW = 'ticket.message.internal_view';

    public const TICKET_MESSAGE_INTERNAL_WRITE = 'ticket.message.internal_write';

    public const TICKET_MESSAGE_RETRY = 'ticket.message.retry';

    public const TICKETS_STATUSES_MANAGE = 'tickets.statuses.manage';

    // ---- attachments (module: attachments) ------------------------------
    public const ATTACHMENTS_UPLOAD = 'attachments.upload';

    public const ATTACHMENTS_DOWNLOAD = 'attachments.download';

    // ---- data protection (module: dataprotection) -------------------------
    public const DATAPROTECTION_ERASURE_EXECUTE = 'dataprotection.erasure.execute';

    public const DATAPROTECTION_RETENTION_VIEW = 'dataprotection.retention.view';

    public const DATAPROTECTION_BACKUP_VIEW = 'dataprotection.backup.view';

    // ---- customers (module: customers) -----------------------------------
    public const CUSTOMERS_VIEW = 'customers.view';

    public const CUSTOMERS_CREATE = 'customers.create';

    public const CUSTOMERS_UPDATE = 'customers.update';

    public const CUSTOMERS_BLOCK = 'customers.block';

    public const CUSTOMERS_CONTACT_MANAGE = 'customers.contact.manage';

    public const CUSTOMERS_NOTE_VIEW = 'customers.note.view';

    public const CUSTOMERS_NOTE_CREATE = 'customers.note.create';

    public const CUSTOMERS_NOTE_DELETE = 'customers.note.delete';

    public const CUSTOMERS_ATTACHMENT_MANAGE = 'customers.attachment.manage';

    public const CUSTOMERS_TIMELINE_VIEW = 'customers.timeline.view';

    public const CUSTOMERS_DUPLICATE_VIEW = 'customers.duplicate.view';

    public const CUSTOMERS_DUPLICATE_REVIEW = 'customers.duplicate.review';

    public const CUSTOMERS_MERGE = 'customers.merge';

    // ---- SLA (module: sla) -----------------------------------------------
    public const SLA_POLICIES_VIEW = 'sla.policies.view';

    public const SLA_POLICIES_MANAGE = 'sla.policies.manage';

    public const SLA_RESET = 'sla.reset';

    public static function all(): array
    {
        $reflection = new ReflectionClass(self::class);
        $keys = [];
        foreach ($reflection->getConstants() as $const) {
            if (is_string($const)) {
                $keys[] = $const;
            }
        }

        return $keys;
    }

    /**
     * @return array{module: string, action: string, scope: ?string}
     */
    public static function parse(string $key): array
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException("Invalid permission key format: {$key}");
        }

        $module = array_shift($parts);
        $lastPart = end($parts);

        try {
            $scope = Scope::from($lastPart);
            array_pop($parts);
            $action = implode('.', $parts);
        } catch (\ValueError) {
            $scope = null;
            $action = implode('.', $parts);
        }

        return [
            'module' => $module,
            'action' => $action,
            'scope' => $scope?->value,
        ];
    }
}
