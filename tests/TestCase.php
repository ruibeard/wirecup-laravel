<?php

namespace Ruibeard\WirecupLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ruibeard\WirecupLaravel\WirecupRenderer;
use Ruibeard\WirecupLaravel\WirecupServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            WirecupServiceProvider::class,
        ];
    }

    protected function renderer(): WirecupRenderer
    {
        return $this->app->make(WirecupRenderer::class);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('wirecup.root', __DIR__.'/Fixtures/wirecup');
        $app['config']->set('wirecup.middleware', ['web']);
    }
}
