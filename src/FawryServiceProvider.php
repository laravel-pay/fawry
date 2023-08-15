<?php

namespace LaravelPay\Fawry;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LaravelPay\Fawry\Commands\FawryCommand;

class FawryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fawry')
            ->hasConfigFile('payment-fawry')
            ->hasViews("fawry")
            ->hasTranslations();
    }
}
