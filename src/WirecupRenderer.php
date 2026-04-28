<?php

namespace Ruibeard\WirecupLaravel;

use Illuminate\Support\Facades\File;

class WirecupRenderer
{
    public function render(string $contents, string $title = 'Wirecup'): string
    {
        $scriptPath = dirname(__DIR__) . '/resources/bin/wirecuprender';
        $cssPath = dirname(__DIR__) . '/resources/bin/wirecup.css';
        $root = rtrim((string) config('wirecup.root'), DIRECTORY_SEPARATOR);

        $python = $this->findPython();

        $command = sprintf(
            '%s %s --render --title %s --css-file %s --root %s --link-prefix %s --link-suffix %s --nav-prefix %s --nav-suffix %s',
            escapeshellcmd($python),
            escapeshellarg($scriptPath),
            escapeshellarg($title),
            escapeshellarg($cssPath),
            escapeshellarg($root),
            escapeshellarg('/wirecup?file='),
            escapeshellarg('.cup'),
            escapeshellarg('/wirecup/render/'),
            escapeshellarg('.cup')
        );

        $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start Wirecup renderer');
        }

        fwrite($pipes[0], $contents);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \InvalidArgumentException(trim($stderr) ?: 'Wirecup render failed');
        }

        $html = $stdout !== false ? $stdout : '';

        return $this->injectIframeScript($html);
    }

    private function findPython(): string
    {
        foreach (['python3', 'python'] as $cmd) {
            $output = [];
            $return = 0;
            @exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null', $output, $return);
            if ($return === 0 && !empty($output)) {
                return $cmd;
            }
        }

        return 'python3';
    }

    private function injectIframeScript(string $html): string
    {
        $script = '<script>(function(){if(window.parent===window)return;document.addEventListener("click",function(e){var a=e.target.closest("a");if(!a)return;var h=a.getAttribute("href");if(h&&h.startsWith("/wirecup/render/")){e.preventDefault();window.parent.location.href=h.replace("/wirecup/render/","/wirecup?file=");}});})();</script>';

        return str_replace('</body>', $script . "\n</body>", $html);
    }

    public function countTokens(string $contents): int
    {
        $binary = $this->findBundledBinary();

        if ($binary !== null && is_executable($binary)) {
            $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
            $process = proc_open(escapeshellcmd($binary), $descriptors, $pipes);

            if (is_resource($process)) {
                fwrite($pipes[0], $contents);
                fclose($pipes[0]);
                $result = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                if ($result !== false && ctype_digit(trim($result))) {
                    return (int) trim($result);
                }
            }
        }

        // Fallback: approximate GPT token count.
        return (int) ceil(mb_strlen($contents) / 4);
    }

    private function findBundledBinary(): ?string
    {
        $binDir = dirname(__DIR__) . '/bin';
        $arch = php_uname('m');
        $os = strtolower(php_uname('s'));

        $map = [
            'darwin' => ['arm64' => 'tokencount-darwin-arm64', 'x86_64' => 'tokencount-darwin-x64'],
            'linux'  => ['arm64' => 'tokencount-linux-arm64', 'x86_64' => 'tokencount-linux-x64'],
        ];

        $osKey = str_contains($os, 'darwin') ? 'darwin' : (str_contains($os, 'linux') ? 'linux' : null);

        if ($osKey === null || !isset($map[$osKey][$arch])) {
            return null;
        }

        $binary = $binDir . '/' . $map[$osKey][$arch];

        return is_executable($binary) ? $binary : null;
    }
}
