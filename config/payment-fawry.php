<?php

// config for LaravelPay/Fawry
return [
    "staging" => [
        'url' => env('FAWRY_STAGING_URL', "https://atfawry.fawrystaging.com/"),
        'secret' => env('FAWRY_STAGING_SECRET'),
        'merchant' => env('FAWRY_STAGING_MERCHANT'),
    ],

    "live" => [
        'url' => env('FAWRY_LIVE_URL', "https://www.atfawry.com/"),
        'secret' => env('FAWRY_LIVE_SECRET'),
        'merchant' => env('FAWRY_LIVE_MERCHANT'),
    ],

    // required allowed values [POPUP, INSIDE_PAGE, SIDE_PAGE , SEPARATED]
    'display_mode' => env('FAWRY_DISPLAY_MODE',"POPUP"),
    // allowed values ['CashOnDelivery', 'PayAtFawry', 'MWALLET', 'CARD' , 'VALU']
    'pay_mode'=>env('FAWRY_PAY_MODE',"CARD"),

    "verify_route_name" => env('FAWRY_VERIFY_ROUTE_NAME', "fawry.verify"),

    "locale" => env('FAWRY_LOCALE', "ar"), // ar or en

    "language" => env('FAWRY_LANGUAGE', "ar-eg"), // ar-eg or en-us
];
