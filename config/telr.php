<?php
return [
    // The current mode is live|production or test
    'test_mode' => env('TELR_TEST_MODE', true),

    // The currency of store

    'currency' => 'AED',

    // The sale endpoint that receive the params
    // @see https://telr.com/support/knowledge-base/hosted-payment-page-integration-guide
    'sale' => [
        'endpoint' => 'https://secure.telr.com/gateway/order.json',
    ],

    // The hosted payment page use the following params as it explained in the integration guide
    // @see https://telr.com/support/knowledge-base/hosted-payment-page-integration-guide/#request-method-and-format
    'create' => [
        'ivp_method' => "create",
        'ivp_store' => '21961',
        'ivp_authkey' => 'DRsmq^MG9m@fjX3z',
        'return_auth' => '/payment_sucess',
        'return_can' => '/payment_cancel',
        'return_decl' => '/payment_declain',
    ]
];