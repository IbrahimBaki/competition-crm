<?php

return [
    'attachments' => [
        'scan_pending' => 'يتم فحص الملف حاليًا وغير متاح بعد',
        'scan_failed' => 'فشل فحص الملف من البرمجيات الخبيثة وتم رفضه',
        'too_large' => 'حجم الملف يتجاوز الحد الأقصى :max كيلوبايت',
        'type_not_allowed' => 'نوع الملف غير مسموح',
    ],
    'bilingual' => [
        'invalid_payload' => 'يجب أن تكون الحقول ثنائية اللغة عبارة عن مصفوفة تحتوي على مفاتيح ar و en.',
    ],
    'organisation' => [
        'branch_has_active_departments' => 'الفرع يحتوي على :count أقسام نشطة',
        'department_has_open_tickets' => 'القسم يحتوي على :count تذاكر مفتوحة',
        'user_branch_not_attached' => 'المستخدم غير مرتبط بهذا الفرع',
    ],
    'security' => [
        'account_deactivated' => 'تم إلغاء تنشيط الحساب',
        'account_locked' => 'الحساب مقفول',
        'administrator_role_locked' => 'لا يمكن تعديل المسؤولين',
        'audit_retention_window_too_short' => 'فترة الاحتفاظ بسجل الفحص أقصر من المدة المحددة كحد أدنى',
        'bot_protection_failed' => 'تم رفض الطلب بواسطة الحماية من الروبوتات',
        'cannot_anonymise_last_administrator' => 'لا يمكن حذف البيانات الشخصية لآخر مسؤول',
        'cannot_deactivate_last_administrator' => 'لا يمكن إلغاء تنشيط آخر مسؤول',
        'cannot_deactivate_self' => 'لا يمكن إلغاء تنشيط حسابك الخاص',
        'invalid_two_factor_code' => 'رمز المصادقة الثنائية غير صحيح',
        'invitation_already_accepted' => 'تم قبول الدعوة بالفعل',
        'invitation_already_pending' => 'دعوة معلقة بالفعل لـ :email',
        'invitation_expired' => 'انتهت صلاحية الدعوة',
        'system_role_immutable' => 'لا يمكن تعديل أدوار النظام',
        'two_factor_already_enabled' => 'المصادقة الثنائية مفعلة بالفعل',
        'two_factor_required' => 'المصادقة الثنائية مطلوبة',
        'user_already_anonymised' => 'تم حذف بيانات المستخدم الشخصية بالفعل',
        'user_already_deactivated' => 'تم إلغاء تنشيط المستخدم بالفعل',
    ],
    'mail' => [
        'invitation' => [
            'subject' => 'أنت مدعو للانضمام إلى Support CRM',
            'greeting' => 'أهلاً وسهلاً',
            'line' => 'أنت مدعو للانضمام إلى نظام Support CRM.',
            'action' => 'قبول الدعوة',
        ],
    ],
];
