<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'يجب أن تقبل السمة: ل.',
    'active_url'           => 'و: سمة ليست URL صالح.',
    'after'                => 'يجب أن يكون سمة تاريخ بعد: التاريخ.',
    'alpha'                => 'قد تحتوي السمة فقط الأحرف: و.',
    'alpha_dash'           => 'قد تحتوي السمة فقط الحروف والأرقام، وشرطات: و.',
    'alpha_num'            => 'قد تحتوي السمة فقط الحروف والأرقام: و.',
    'array'                => 'يجب أن يكون سمة صفيف: و.',
    'before'               => 'يجب أن يكون سمة تاريخ قبل: التاريخ.',
    'between'              => [
        'numeric' => 'يجب أن تكون السمة بين: في دقيقة و: ماكس.',
        'file'    => 'يجب أن تكون السمة بين: في دقيقة و: كيلوبايت كحد أقصى.',
        'string'  => 'يجب أن تكون السمة بين: في دقيقة و: حرف كحد أقصى.',
        'array'   => 'يجب أن يكون السمة بين: في دقيقة و: البنود كحد أقصى.',
    ],
    'boolean'              => 'يجب أن يكون حقل السمة صحيحة أو خاطئة: و.',
    'confirmed'            => 'لا يتطابق تأكيدا السمة: ل.',
    'date'                 => 'و: سمة ليست تاريخا صالحا.',
    'date_format'          => 'و: سمة لا يطابق الشكل: شكل.',
    'different'            => 'يجب أن يكون البعض مختلف:: سمة و.',
    'digits'               => 'يجب أن يكون سمة: أرقام أرقام.',
    'digits_between'       => 'يجب أن تكون السمة بين: في دقيقة و: ماكس أرقام.',
    'email'                => 'يجب أن يكون سمة عنوان بريد إلكتروني صالح: ل.',
    'exists'               => 'المختار: سمة غير صحيحة.',
    'filled'               => 'مطلوب حقل السمة: ل.',
    'image'                => 'يجب أن يكون سمة صورة: ل.',
    'in'                   => 'المختار: سمة غير صحيحة.',
    'integer'              => 'يجب أن يكون سمة عدد صحيح: و.',
    'ip'                   => 'يجب أن يكون سمة عنوان IP صالح: ل.',
    'json'                 => 'يجب أن يكون سمة سلسلة JSON صالحة: و.',
    'max'                  => [
        'numeric' => 'قد لا تكون السمة أكبر من: أقصى الحدود.',
        'file'    => 'قد لا تكون السمة أكبر من: لكيلو بايت كحد أقصى.',
        'string'  => 'قد لا تكون السمة أكبر من: الأحرف كحد أقصى.',
        'array'   => 'قد لا يكون أكثر من سمة: البنود كحد أقصى.',
    ],
    'mimes'                => 'يجب أن يكون سمة ملف من نوع: ل: القيم.',
    'min'                  => [
        'numeric' => 'يجب أن يكون سمة على الأقل: دقيقة.',
        'file'    => 'يجب أن يكون سمة على الأقل: على مين كيلوبايت.',
        'string'  => 'يجب أن يكون سمة على الأقل: الأحرف نان.',
        'array'   => 'يجب أن يكون سمة على الأقل: على مين البنود.',
    ],
    'not_in'               => 'المختار: سمة غير صحيحة.',
    'numeric'              => 'يجب أن تكون السمة عدد: لل.',
    'regex'                => 'و: شكل سمة غير صالح.',
    'required'             => 'ال :attribute الحقل مطلوب.',
    'required_if'          => 'مطلوب حقل السمة عندما: الآخر هو: قيمة.',
    'required_unless'      => 'مطلوب حقل السمة إلا إذا: الآخر في: القيم.',
    'required_with'        => 'مطلوب حقل السمة عندما: القيم موجودة.',
    'required_with_all'    => 'مطلوب حقل السمة عندما: القيم موجودة.',
    'required_without'     => 'مطلوب حقل السمة عندما: القيم غير موجودة.',
    'required_without_all' => 'مطلوب حقل السمة عندما لا شيء: القيم موجودة.',
    'same'                 => 'و: سمة و: أخرى يجب أن تتطابق.',
    'size'                 => [
        'numeric' => 'يجب أن يكون سمة: حجم.',
        'file'    => 'يجب أن يكون سمة: لكيلو بايت الحجم.',
        'string'  => 'يجب أن يكون سمة: حجم الأحرف.',
        'array'   => 'يجب أن يحتوي على سمة: البنود الحجم.',
    ],
    'string'               => 'يجب أن يكون سمة سلسلة: ل.',
    'timezone'             => 'يجب أن يكون سمة منطقة صالحة: و.',
    'unique'               => 'وقد تم بالفعل اتخاذ السمة: ل.',
    'url'                  => 'و: شكل سمة غير صالح.',
	'ccn'                  => 'و: سمة غير صحيحة.',
    'ccd'                  => 'و: سمة غير صحيحة.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'العرف رسالة',
        ],
    ],
	'custom' => array(
    'address_type' => array(
        'required' => 'مطلوب نوع العنوان',
		),
	'email' => array(
        'required' => 'مطلوب نوع العنوان',
        'email' => 'مطلوب نوع العنوان',
		),
	),

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
