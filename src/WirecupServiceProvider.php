<?php

namespace Ruibeard\LaravelWirecup;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class WirecupServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wirecup');

        $this->mergeConfigFrom(__DIR__.'/../config/wirecup.php', 'wirecup');

        $this->publishes([
            __DIR__.'/../config/wirecup.php' => config_path('wirecup.php'),
        ], 'wirecup-config');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->installAgentFiles();
    }

    public function installAgentFiles(): void
    {
        $skill = base_path('.agents/skills/wirecup/SKILL.md');

        if (File::exists($skill)) {
            return;
        }

        File::ensureDirectoryExists(dirname($skill));
        File::copy(__DIR__.'/../resources/skills/wirecup/SKILL.md', $skill);
        File::ensureDirectoryExists(base_path('.agents/.cup'));
    }
}
