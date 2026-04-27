<?php

namespace Ruibeard\LaravelWirecup\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ruibeard\LaravelWirecup\Tests\TestCase;

class WirecupRouteTest extends TestCase
{
    public function test_it_auto_installs_the_skill_on_boot(): void
    {
        $basePath = sys_get_temp_dir().'/laravel-wirecup-auto-'.bin2hex(random_bytes(8));

        File::ensureDirectoryExists($basePath);
        $this->app->setBasePath($basePath);

        try {
            $provider = $this->app->getProvider(\Ruibeard\LaravelWirecup\WirecupServiceProvider::class);
            $provider->installAgentFiles();

            $this->assertFileExists($basePath.'/.agents/skills/wirecup/SKILL.md');
            $this->assertDirectoryExists($basePath.'/.agents/.cup');
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
