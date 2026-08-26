<?php

namespace Database\Seeders;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationEventType;
use App\Domains\Notifications\Models\NotificationTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            $this->command?->warn('Notification templates table not yet migrated; skipping templates.');

            return;
        }

        $templates = [
            [
                'code' => NotificationEventType::SlaWarning->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'تنبيه SLA - {{sla_metric}}',
                'en_subject' => 'SLA Warning - {{sla_metric}}',
                'ar_body' => 'تنبيه بشأن تذكرة {{ticket_reference}}: متبقي {{minutes_until_breach}} دقيقة قبل انتهاك {{sla_metric}}.',
                'en_body' => 'Warning about ticket {{ticket_reference}}: {{minutes_until_breach}} minutes remaining before {{sla_metric}} breach.',
            ],
            [
                'code' => NotificationEventType::SlaWarning->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'تنبيه SLA - {{sla_metric}}',
                'en_subject' => 'SLA Warning - {{sla_metric}}',
                'ar_body' => 'تنبيه بشأن تذكرة {{ticket_reference}}: متبقي {{minutes_until_breach}} دقيقة قبل انتهاك {{sla_metric}}.',
                'en_body' => 'Warning about ticket {{ticket_reference}}: {{minutes_until_breach}} minutes remaining before {{sla_metric}} breach.',
            ],
            [
                'code' => NotificationEventType::SlaBreach->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'انتهاك SLA - {{sla_metric}}',
                'en_subject' => 'SLA Breach - {{sla_metric}}',
                'ar_body' => 'تم انتهاك {{sla_metric}} للتذكرة {{ticket_reference}}.',
                'en_body' => '{{sla_metric}} has been breached for ticket {{ticket_reference}}.',
            ],
            [
                'code' => NotificationEventType::SlaBreach->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'انتهاك SLA - {{sla_metric}}',
                'en_subject' => 'SLA Breach - {{sla_metric}}',
                'ar_body' => 'تم انتهاك {{sla_metric}} للتذكرة {{ticket_reference}}.',
                'en_body' => '{{sla_metric}} has been breached for ticket {{ticket_reference}}.',
            ],
            [
                'code' => NotificationEventType::TicketAssigned->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'تم إسناد تذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Assigned',
                'ar_body' => 'تم إسناد التذكرة {{ticket_reference}} من العميل {{customer_name}} إليك.',
                'en_body' => 'Ticket {{ticket_reference}} from {{customer_name}} has been assigned to you.',
            ],
            [
                'code' => NotificationEventType::TicketAssigned->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'تم إسناد تذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Assigned',
                'ar_body' => 'تم إسناد التذكرة {{ticket_reference}} من العميل {{customer_name}} إليك.',
                'en_body' => 'Ticket {{ticket_reference}} from {{customer_name}} has been assigned to you.',
            ],
            [
                'code' => NotificationEventType::TicketTransferred->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'تم تحويل التذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Transferred',
                'ar_body' => 'تم تحويل التذكرة {{ticket_reference}} إلى {{transfer_target}}.',
                'en_body' => 'Ticket {{ticket_reference}} has been transferred to {{transfer_target}}.',
            ],
            [
                'code' => NotificationEventType::TicketTransferred->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'تم تحويل التذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Transferred',
                'ar_body' => 'تم تحويل التذكرة {{ticket_reference}} إلى {{transfer_target}}.',
                'en_body' => 'Ticket {{ticket_reference}} has been transferred to {{transfer_target}}.',
            ],
            [
                'code' => NotificationEventType::TicketEscalated->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'تم ترقية التذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Escalated',
                'ar_body' => 'تم ترقية التذكرة {{ticket_reference}}: {{reason}}',
                'en_body' => 'Ticket {{ticket_reference}} has been escalated: {{reason}}',
            ],
            [
                'code' => NotificationEventType::TicketEscalated->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'تم ترقية التذكرة {{ticket_reference}}',
                'en_subject' => 'Ticket {{ticket_reference}} Escalated',
                'ar_body' => 'تم ترقية التذكرة {{ticket_reference}}: {{reason}}',
                'en_body' => 'Ticket {{ticket_reference}} has been escalated: {{reason}}',
            ],
            [
                'code' => NotificationEventType::TicketMessagePosted->value,
                'channel' => NotificationChannel::InApp->value,
                'ar_subject' => 'رسالة جديدة على التذكرة {{ticket_reference}}',
                'en_subject' => 'New Message on Ticket {{ticket_reference}}',
                'ar_body' => 'رسالة جديدة من {{author}} على التذكرة {{ticket_reference}}: {{preview}}',
                'en_body' => 'New message from {{author}} on ticket {{ticket_reference}}: {{preview}}',
            ],
            [
                'code' => NotificationEventType::UserInvited->value,
                'channel' => NotificationChannel::Mail->value,
                'ar_subject' => 'أنت مدعو للانضمام إلى Support CRM',
                'en_subject' => 'You are invited to join Support CRM',
                'ar_body' => 'أنت مدعو للانضمام إلى نظام Support CRM. انقر على الرابط أدناه لقبول الدعوة.',
                'en_body' => 'You are invited to join Support CRM. Click the link below to accept the invitation.',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(
                [
                    'code' => $template['code'],
                    'channel' => $template['channel'],
                ],
                [
                    'subject' => [
                        'ar' => $template['ar_subject'],
                        'en' => $template['en_subject'],
                    ],
                    'body' => [
                        'ar' => $template['ar_body'],
                        'en' => $template['en_body'],
                    ],
                    'is_active' => true,
                ],
            );
        }
    }
}
