# This is my package fawry

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-pay/fawry.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/fawry)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-pay/fawry.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/fawry)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require laravel-pay/fawry
```

You can publish and run the Translations with:

```bash
php artisan vendor:publish --tag="fawry-translations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fawry-config"
```

This is the contents of the published config file:

```php
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

```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="fawry-views"
```

## Usage


1. add this to your .env file
```php
FAWRY_STAGING_MERCHANT=""
FAWRY_STAGING_SECRET=""
```
2. Pay Route
```php
Route::get("/fawry" , function(){
    $form = Fawry::setOnStagingMode()
        ->setAmount(100.12)
        ->setUserId(11111)
        ->setUserFirstName("ahmed")
        ->setUserLastName("elsayed")
        ->setUserEmail("ahmed_elsayed@gmail.com")
        ->setUserPhone("01000000000")
        ->pay();

    return view("welcome" , [
        "form" => $form['html']
    ]);
});
```

3. Verify Route <br>
**_note : you can change the verify route name in config file_**

```php
Route::get("/fawry/verify" , function(){
    $response = Fawry::setOnStagingMode()->verify();
    dd($response);
})->name("fawry.verify");

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [elsayed kamal](https://github.com/laravel-pay)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
