<?php

namespace Ruibeard\WirecupLaravel\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Ruibeard\WirecupLaravel\Tests\TestCase;

class WirecupRouteTest extends TestCase
{
    public function test_install_command_downloads_skill_from_github(): void
    {
        Http::fake([
            '*' => Http::response('# Downloaded SKILL', 200),
        ]);

        $basePath = sys_get_temp_dir().'/laravel-wirecup-install-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($basePath);
        $this->app->setBasePath($basePath);

        try {
            $this->artisan('wirecup:install')
                ->expectsConfirmation('Publish wirecup config to config/wirecup.php?', 'no')
                ->assertExitCode(0);

            $this->assertFileExists($basePath.'/.agents/skills/wirecup/SKILL.md');
            $this->assertStringContainsString('Downloaded SKILL', File::get($basePath.'/.agents/skills/wirecup/SKILL.md'));
            $this->assertDirectoryExists($basePath.'/.agents/.cup');
        } finally {
            File::deleteDirectory($basePath);
        }
    }

    public function test_install_command_falls_back_to_bundled_skill_on_failure(): void
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $basePath = sys_get_temp_dir().'/laravel-wirecup-fallback-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($basePath);
        $this->app->setBasePath($basePath);

        try {
            $this->artisan('wirecup:install')
                ->expectsConfirmation('Publish wirecup config to config/wirecup.php?', 'no')
                ->assertExitCode(0);

            $this->assertFileExists($basePath.'/.agents/skills/wirecup/SKILL.md');
        } finally {
            File::deleteDirectory($basePath);
        }
    }

    public function test_index_lists_cup_files(): void
    {
        $this
            ->get('/wirecup')
            ->assertOk()
            ->assertSee('home.cup', false);
    }

    public function test_render_returns_html_for_cup_file(): void
    {
        $this
            ->get('/wirecup/render/home.cup')
            ->assertOk()
            ->assertSee('Welcome Home', false);
    }

    public function test_render_returns_404_for_unknown_file(): void
    {
        $this->get('/wirecup/render/nope.cup')->assertNotFound();
    }

    public function test_render_rejects_path_traversal(): void
    {
        $this->get('/wirecup/render/../../etc/passwd')->assertNotFound();
    }
}
