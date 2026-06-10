<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'لازم :attribute يكون بريد إلكتروني صحيح.',
    'string' => 'لازم :attribute يكون نص.',
    'min' => [
        'string' => 'لازم :attribute يكون :min حروف على الأقل.',
        'numeric' => 'لازم :attribute يكون :min على الأقل.',
    ],
    'max' => [
        'string' => 'لازم :attribute ما يزيدش عن :max حرف.',
    ],
    'confirmed' => 'تأكيد :attribute مش متطابق.',
    'unique' => ':attribute مستخدم قبل كده.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'lowercase' => 'لازم :attribute يكون بحروف صغيرة.',

    // Friendly Arabic field names used inside the messages above.
    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'code' => 'كود الوصول',
        'title' => 'العنوان',
        'price' => 'السعر',
    ],
];
