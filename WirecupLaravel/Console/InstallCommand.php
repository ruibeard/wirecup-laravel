<?php

namespace Ruibeard\WirecupLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class InstallCommand extends Command
{
    const SKILL_URL = 'https://raw.githubusercontent.com/ruibeard/wirecup/main/.agents/skills/wirecup/SKILL.md';

    protected $signature = 'wirecup:install';

    protected $description = 'Install Wirecup: create .agents/.cup folder and install the Wirecup skill';

    public function handle(): int
    {
        $this->info('Installing Wirecup...');

        // Create .agents/.cup directory
        $cupDir = base_path('.agents/.cup');
        if (! File::isDirectory($cupDir)) {
            File::makeDirectory($cupDir, 0755, true);
            $this->info('Created .agents/.cup/');
        } else {
            $this->line('  .agents/.cup/ already exists, skipping.');
        }

        // Download skill from wirecup repo, fall back to bundled copy
        $skill = base_path('.agents/skills/wirecup/SKILL.md');
        File::ensureDirectoryExists(dirname($skill));

        $this->downloadSkill($skill);

        // Optionally publish config
        if ($this->confirm('Publish wirecup config to config/wirecup.php?', false)) {
            $this->callSilent('vendor:publish', ['--tag' => 'wirecup-config']);
            $this->info('Published config/wirecup.php');
        }

        $this->newLine();
        $this->info('Wirecup installed. Visit /wirecup to preview your .cup files.');

        return self::SUCCESS;
    }

    protected function downloadSkill(string $destination): void
    {
        $this->line('  Downloading latest skill from '.self::SKILL_URL.' ...');

        try {
            $response = Http::timeout(10)->get(self::SKILL_URL);

            if ($response->successful()) {
                File::put($destination, $response->body());
                $this->info('Installed skill → .agents/skills/wirecup/SKILL.md');

                return;
            }

            $this->warn('  Download failed (HTTP '.$response->status().'), falling back to bundled skill.');
        } catch (\Throwable $e) {
            $this->warn('  Download failed ('.$e->getMessage().'), falling back to bundled skill.');
        }

        File::copy(__DIR__.'/../../resources/skills/wirecup/SKILL.md', $destination);
        $this->info('Installed skill (bundled) → .agents/skills/wirecup/SKILL.md');
    }
}
