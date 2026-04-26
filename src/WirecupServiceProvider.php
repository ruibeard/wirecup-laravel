<?php

namespace Ruibeard\LaravelWirecup;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WirecupServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-wirecup')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WirecupRenderer::class, fn (): WirecupRenderer => new WirecupRenderer());

        $this->app->singleton(Wirecup::class, function ($app): Wirecup {
            return new Wirecup($app['files'], $app[WirecupRenderer::class]);
        });
    }
}
