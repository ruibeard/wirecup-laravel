<?php

namespace Ruibeard\LaravelWirecup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WirecupServiceProvider extends PackageServiceProvider
{
    protected function skillSourcePath(): string
    {
        return dirname(__DIR__).'/resources/skills/wirecup/SKILL.md';
    }

    public function installAgentFiles(): void
    {
        $skillPath = base_path('.agents/skills/wirecup/SKILL.md');

        File::ensureDirectoryExists(dirname($skillPath));

        if (! File::exists($skillPath)) {
            File::copy($this->skillSourcePath(), $skillPath);
        }

        File::ensureDirectoryExists(base_path('.agents/.cup'));

        $gitkeep = base_path('.agents/.cup/.gitkeep');

        if (! File::exists($gitkeep)) {
            File::put($gitkeep, '');
        }
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-wirecup')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasInstallCommand(function (Command $command): void {
                $command
                    ->publishConfigFile()
                    ->startWith(function (Command $command): void {
                        $command->comment('Installing Wirecup agent files...');
                        $this->installAgentFiles();
                    })
                    ->endWith(function (Command $command): void {
                        $command->info('Wirecup skill published to .agents/skills/wirecup/SKILL.md');
                        $command->info('Create mockups in .agents/.cup and open /wirecup');
                    });
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WirecupRenderer::class, fn (): WirecupRenderer => new WirecupRenderer());

        $this->app->singleton(Wirecup::class, function ($app): Wirecup {
            return new Wirecup($app['files'], $app[WirecupRenderer::class]);
        });
    }

    public function packageBooted(): void
    {
        $this->publishes([
            $this->skillSourcePath() => base_path('.agents/skills/wirecup/SKILL.md'),
        ], 'wirecup-skill');

        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            return;
        }

        $this->installAgentFiles();
    }
}
