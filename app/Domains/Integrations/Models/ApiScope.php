<?php

namespace App\Domains\Integrations\Models;

use App\Domains\Security\Permissions\PermissionKey;

enum ApiScope: string
{
    case TicketsRead = 'tickets.read';
    case TicketsWrite = 'tickets.write';
    case CustomersRead = 'customers.read';
    case KnowledgeRead = 'knowledge.read';
    case ReportsRead = 'reports.read';
    case WebhooksManage = 'webhooks.manage';

    public function permission(): PermissionKey
    {
        return match ($this) {
            self::TicketsRead => PermissionKey::TICKETS_VIEW_ANY,
            self::TicketsWrite => PermissionKey::TICKETS_UPDATE,
            self::CustomersRead => PermissionKey::CUSTOMERS_VIEW,
            self::KnowledgeRead => PermissionKey::KNOWLEDGE_ARTICLES_VIEW,
            self::ReportsRead => PermissionKey::REPORTS_VIEW_ANY,
            self::WebhooksManage => PermissionKey::INTEGRATIONS_WEBHOOKS_MANAGE,
        };
    }
}
