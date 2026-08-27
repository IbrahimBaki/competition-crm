<?php

namespace Database\Seeders;

use App\Domains\Channels\Messaging\Models\ProviderMessageTemplate;
use App\Domains\Ticketing\Models\MessageChannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProviderMessageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'channel' => MessageChannel::Whatsapp,
                'key' => 'order_status',
                'body_en' => 'Hello {{customer_name}}, your order {{order_id}} status is {{status}}. Thank you for your business!',
                'body_ar' => 'مرحباً {{customer_name}}، حالة طلبك {{order_id}} هي {{status}}. شكراً لك على تعاملك معنا!',
                'variables' => ['customer_name', 'order_id', 'status'],
                'provider_template_name' => 'order_status_notification',
                'is_active' => true,
                'approved_at' => now(),
            ],
            [
                'channel' => MessageChannel::Whatsapp,
                'key' => 'appointment_reminder',
                'body_en' => 'Hi {{name}}, this is a reminder for your appointment on {{date}} at {{time}}. Please confirm by replying YES or NO.',
                'body_ar' => 'مرحباً {{name}}، تذكير بموعد حضورك في {{date}} الساعة {{time}}. الرجاء التأكيد بالرد نعم أو لا.',
                'variables' => ['name', 'date', 'time'],
                'provider_template_name' => 'appointment_reminder',
                'is_active' => true,
                'approved_at' => now(),
            ],
            [
                'channel' => MessageChannel::Sms,
                'key' => 'otp_verification',
                'body_en' => 'Your verification code is {{code}}. Do not share this code with anyone.',
                'body_ar' => 'رمز التحقق الخاص بك هو {{code}}. لا تشارك هذا الرمز مع أي شخص.',
                'variables' => ['code'],
                'provider_template_name' => 'otp_code',
                'is_active' => true,
                'approved_at' => now(),
            ],
            [
                'channel' => MessageChannel::Sms,
                'key' => 'delivery_notification',
                'body_en' => 'Your package {{tracking_id}} has been delivered. Thank you!',
                'body_ar' => 'تم تسليم طردك {{tracking_id}}. شكراً لك!',
                'variables' => ['tracking_id'],
                'provider_template_name' => 'delivery_notification',
                'is_active' => true,
                'approved_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            ProviderMessageTemplate::updateOrCreate(
                ['channel' => $template['channel'], 'key' => $template['key']],
                array_merge($template, ['uuid' => Str::uuid()])
            );
        }
    }
}
