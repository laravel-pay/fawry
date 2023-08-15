<?php

namespace LaravelPay\Fawry\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelPay\Fawry\Fawry
 */
class Fawry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LaravelPay\Fawry\Fawry::class;
    }
}
