<?php

return [
    'singular_label' => 'إصدار التطبيق',
    'plural_label' => 'إصدارات التطبيق',
    'navigation_label' => 'إصدارات التطبيق',
    'navigation_group' => 'إدارة الإصدارات',

    'sections' => [
        'version_info' => 'معلومات الإصدار',
        'release_info' => 'معلومات الإطلاق',
        'settings' => 'الإعدادات',
        'metadata' => 'بيانات إضافية',
    ],

    'fields' => [
        'version' => 'الإصدار',
        'build_number' => 'رقم البناء',
        'platform' => 'المنصة',
        'minimum_required_version' => 'الحد الأدنى للإصدار المطلوب',
        'release_date' => 'تاريخ الإطلاق',
        'download_url' => 'رابط التحميل',
        'release_notes' => 'ملاحظات الإصدار',
        'language' => 'اللغة',
        'notes' => 'الملاحظات',
        'is_active' => 'نشط',
        'force_update' => 'تحديث إجباري',
        'is_beta' => 'إصدار تجريبي',
        'is_rollback' => 'إصدار استرجاع',
        'metadata' => 'البيانات الوصفية',
        'key' => 'المفتاح',
        'value' => 'القيمة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'created_by' => 'أنشئ بواسطة',
        'updated_by' => 'حُدث بواسطة',
    ],

    'columns' => [
        'version' => 'الإصدار',
        'platform' => 'المنصة',
        'release_date' => 'تاريخ الإطلاق',
        'is_active' => 'نشط',
        'force_update' => 'تحديث إجباري',
        'is_beta' => 'تجريبي',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],

    'placeholders' => [
        'version' => 'مثال: 1.0.0',
        'build_number' => 'مثال: 100',
        'minimum_required_version' => 'مثال: 0.9.0',
        'download_url' => 'https://example.com/download',
        'release_notes' => 'أدخل ملاحظات الإصدار باللغة :language...',
    ],

    'help' => [
        'version' => 'استخدم تنسيق الإصدار الدلالي (مثال: 1.0.0)',
        'build_number' => 'رقم البناء الداخلي للتتبع',
        'platform' => 'المنصة المستهدفة لهذا الإصدار',
        'minimum_required_version' => 'الحد الأدنى للإصدار المطلوب للتحديث إلى هذا الإصدار',
        'release_date' => 'التاريخ الذي تم/سيتم فيه إطلاق هذا الإصدار',
        'download_url' => 'الرابط حيث يمكن للمستخدمين تحميل هذا الإصدار',
        'release_notes' => 'أضف ملاحظات الإصدار بلغات متعددة',
        'is_active' => 'ما إذا كان هذا الإصدار نشطًا ومتاحًا حاليًا',
        'force_update' => 'ما إذا كان يجب على المستخدمين التحديث إلى هذا الإصدار',
        'is_beta' => 'وضع علامة على هذا كإصدار تجريبي/اختبار',
        'is_rollback' => 'وضع علامة على هذا كإصدار استرجاع',
        'metadata' => 'بيانات وصفية إضافية بتنسيق مفتاح-قيمة',
    ],

    "platforms" => [
        'ios' => 'آي او اس',
        'android' => 'أندرويد',
    ],

    'filters' => [
        'platform' => 'المنصة',
        'is_active' => 'حالة النشاط',
        'force_update' => 'تحديث إجباري',
        'is_beta' => 'إصدار تجريبي',
    ],

    'tabs' => [
        'all' => 'الكل',
    ],

    'actions' => [
        'create' => 'إصدار جديد',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'duplicate' => 'نسخ',
        'create_rollback' => 'إنشاء استرجاع',
    ],

    'messages' => [
        'created' => 'تم إنشاء إصدار التطبيق بنجاح',
        'updated' => 'تم تحديث إصدار التطبيق بنجاح',
        'deleted' => 'تم حذف إصدار التطبيق بنجاح',
        'duplicate_created' => 'تم نسخ الإصدار بنجاح',
        'rollback_created' => 'تم إنشاء إصدار الاسترجاع بنجاح',
        'copied' => 'تم نسخ الإصدار إلى الحافظة',
    ],

    'confirmations' => [
        'delete' => 'هل أنت متأكد من أنك تريد حذف هذا الإصدار؟',
        'duplicate' => 'سيؤدي هذا إلى إنشاء نسخة من الإصدار الحالي مع رقم إصدار متزايد.',
        'create_rollback' => 'سيؤدي هذا إلى إنشاء إصدار استرجاع بناءً على الإصدار الحالي.',
    ],

    'notifications' => [
        'force_update_created' => [
            'title' => 'تم إنشاء تحديث إجباري',
            'body' => 'تم إنشاء الإصدار :version للمنصة :platform كتحديث إجباري.',
        ],
        'beta_version_created' => [
            'title' => 'تم إنشاء إصدار تجريبي',
            'body' => 'تم إنشاء الإصدار التجريبي :version للمنصة :platform.',
        ],
        'force_update_enabled' => [
            'title' => 'تم تفعيل التحديث الإجباري',
            'body' => 'الإصدار :version للمنصة :platform مُعلم الآن كتحديث إجباري.',
        ],
        'version_activated' => [
            'title' => 'تم تفعيل الإصدار',
            'body' => 'تم تفعيل الإصدار :version للمنصة :platform.',
        ],
    ],

    'validation' => [
        'version_format' => 'يجب أن يتبع الإصدار تنسيق الإصدار الدلالي (مثال: 1.0.0)',
        'minimum_version_format' => 'يجب أن يتبع الحد الأدنى للإصدار المطلوب تنسيق الإصدار الدلالي',
        'unique_version_platform' => 'هذا الإصدار موجود بالفعل للمنصة المحددة',
    ],

    'empty_state' => [
        'heading' => 'لا توجد إصدارات تطبيق',
        'description' => 'ابدأ بإنشاء إصدار التطبيق الأول.',
    ],
];
