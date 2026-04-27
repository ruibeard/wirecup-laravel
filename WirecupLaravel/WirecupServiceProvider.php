<?php

namespace Ruibeard\WirecupLaravel;

use Illuminate\Support\ServiceProvider;
use Ruibeard\WirecupLaravel\Console\InstallCommand;

class WirecupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/wirecup.php', 'wirecup');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wirecup');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../config/wirecup.php' => config_path('wirecup.php'),
        ], 'wirecup-config');

        $this->publishes([
            __DIR__.'/../resources/skills/wirecup/SKILL.md' => base_path('.agents/skills/wirecup/SKILL.md'),
        ], 'wirecup-skill');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
