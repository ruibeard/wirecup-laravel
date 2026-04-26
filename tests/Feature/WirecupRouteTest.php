<?php

namespace Ruibeard\LaravelWirecup\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Ruibeard\LaravelWirecup\Tests\TestCase;

class WirecupRouteTest extends TestCase
{
    public function test_it_auto_installs_the_skill_files(): void
    {
        $basePath = sys_get_temp_dir().'/laravel-wirecup-auto-'.bin2hex(random_bytes(8));

        File::ensureDirectoryExists($basePath);
        $this->app->setBasePath($basePath);

        try {
            $provider = $this->app->getProvider(\Ruibeard\LaravelWirecup\WirecupServiceProvider::class);
            $provider->installAgentFiles();

            $this->assertFileExists($basePath.'/.agents/skills/wirecup/SKILL.md');
            $this->assertFileExists($basePath.'/.agents/.cup/.gitkeep');
        } finally {
            File::deleteDirectory($basePath);
        }
    }

    public function test_it_lists_wirecup_files(): void
    {
        $this
            ->get('/wirecup')
            ->assertOk()
            ->assertSee('home.cup', false)
            ->assertSee('admin/dashboard.cup', false);
    }

    public function test_it_renders_wirecup_html(): void
    {
        $this
            ->get('/wirecup/render/home.cup')
            ->assertOk()
            ->assertSee('Welcome Home', false)
            ->assertSee('/wirecup/render/admin/dashboard.cup', false);
    }

    public function test_it_rewrites_root_relative_and_named_wirecup_links(): void
    {
        $homePath = __DIR__.'/../Fixtures/wirecup/home.cup';
        $dashboardPath = __DIR__.'/../Fixtures/wirecup/dashboard.cup';
        $originalHome = file_get_contents($homePath);
        $hadDashboard = is_file($dashboardPath);
        $originalDashboard = $hadDashboard ? file_get_contents($dashboardPath) : null;

        try {
            file_put_contents($homePath, <<<'CUP'
n Home|home Dashboard|dashboard External|https://example.com
b Open dashboard|admin/dashboard
b Home absolute|/home
CUP);

            file_put_contents($dashboardPath, <<<'CUP'
h Dashboard
CUP);

            $this
                ->get('/wirecup/render/home.cup')
                ->assertOk()
                ->assertSee('/wirecup/render/home.cup', false)
                ->assertSee('/wirecup/render/dashboard.cup', false)
                ->assertSee('/wirecup/render/admin/dashboard.cup', false)
                ->assertSee('https://example.com', false);
        } finally {
            file_put_contents($homePath, $originalHome === false ? '' : $originalHome);

            if ($hadDashboard) {
                file_put_contents($dashboardPath, $originalDashboard === false ? '' : (string) $originalDashboard);
            } elseif (is_file($dashboardPath)) {
                unlink($dashboardPath);
            }
        }
    }

    public function test_it_installs_the_skill_and_mock_directory(): void
    {
        $basePath = sys_get_temp_dir().'/laravel-wirecup-test-'.bin2hex(random_bytes(8));

        File::ensureDirectoryExists($basePath);
        $this->app->setBasePath($basePath);

        try {
            Artisan::call('wirecup:install');

            $this->assertFileExists($basePath.'/.agents/skills/wirecup/SKILL.md');
            $this->assertFileExists($basePath.'/.agents/.cup/.gitkeep');
        } finally {
            File::deleteDirectory($basePath);
        }
    }
}
