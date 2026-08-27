<?php

namespace App\Domains\Integrations\Models;

enum WebhookDeliveryState: string
{
    case Pending = 'pending';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Failed = 'failed';
}
