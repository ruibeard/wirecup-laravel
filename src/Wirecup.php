<?php

namespace Ruibeard\LaravelWirecup;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Wirecup
{
    public function __construct(
        protected Filesystem $files,
        protected WirecupRenderer $renderer,
    ) {
    }

    /**
     * @return list<string>
     */
    public function files(): array
    {
        $root = $this->root();

        if (! is_dir($root)) {
            return [];
        }

        $files = [];

        foreach ($this->files->allFiles($root) as $file) {
            if ($file->getExtension() !== 'cup') {
                continue;
            }

            $files[] = str_replace('\\', '/', $file->getRelativePathname());
        }

        sort($files);

        return array_values($files);
    }

    public function selectedFile(?string $requested = null): ?string
    {
        $requested = $this->normalizeRelativePath($requested);

        if ($requested !== null && $this->exists($requested)) {
            return $requested;
        }

        $default = $this->normalizeRelativePath(config('wirecup.default_file'));

        if ($default !== null && $this->exists($default)) {
            return $default;
        }

        return $this->files()[0] ?? null;
    }

    public function previewUrl(string $relativePath): string
    {
        $uri = trim((string) config('wirecup.uri', 'wirecup'), '/');
        $encodedPath = implode('/', array_map(rawurlencode(...), explode('/', $relativePath)));

        return '/'.$uri.'/render/'.$encodedPath;
    }

    public function render(?string $requested = null): string
    {
        $selected = $this->selectedFile($requested);

        if ($selected === null) {
            abort(404, 'No Wirecup files were found.');
        }

        $absolutePath = $this->absolutePath($selected);
        $title = Str::beforeLast(basename($selected), '.cup');
        $contents = (string) file_get_contents($absolutePath);
        $html = $this->renderer->render($contents, $title);

        return $this->rewriteLinks($html, $selected);
    }

    public function root(): string
    {
        return (string) config('wirecup.root', base_path('.agents/.cup'));
    }

    protected function exists(string $relativePath): bool
    {
        return is_file($this->absolutePath($relativePath));
    }

    protected function absolutePath(string $relativePath): string
    {
        $root = realpath($this->root());

        if ($root === false) {
            abort(404, 'The Wirecup root directory does not exist.');
        }

        $candidate = realpath($root.DIRECTORY_SEPARATOR.$relativePath);

        if ($candidate === false || (! str_starts_with($candidate, $root.DIRECTORY_SEPARATOR) && $candidate !== $root)) {
            abort(404, 'The requested Wirecup file could not be resolved.');
        }

        if (pathinfo($candidate, PATHINFO_EXTENSION) !== 'cup') {
            abort(404, 'Only .cup files can be rendered.');
        }

        return $candidate;
    }

    protected function rewriteLinks(string $html, string $currentFile): string
    {
        return (string) preg_replace_callback('/href=("|\')(.*?)\1/i', function (array $matches) use ($currentFile): string {
            $href = $matches[2];

            if (
                $href === ''
                || str_starts_with($href, '#')
                || str_starts_with($href, '//')
                || preg_match('/^[a-z][a-z0-9+.-]*:/i', $href)
            ) {
                return $matches[0];
            }

            [$path, $suffix] = $this->splitHref($href);

            if (str_starts_with($path, '/')) {
                $resolved = $this->normalizeRelativePath(Str::finish(ltrim($path, '/'), '.cup'));
            } elseif (str_ends_with($path, '.html')) {
                $resolved = $this->normalizeRelativePath(Str::replaceLast('.html', '.cup', $path));
            } elseif (str_ends_with($path, '.cup')) {
                $currentDir = str_replace('\\', '/', dirname($currentFile));
                $currentDir = $currentDir === '.' ? '' : trim($currentDir, '/');
                $resolved = $this->normalizeRelativePath(ltrim(($currentDir !== '' ? $currentDir.'/' : '').$path, '/'));
            } else {
                $resolved = $this->normalizeRelativePath(Str::finish($path, '.cup'));
            }

            if ($resolved === null || ! $this->exists($resolved)) {
                return $matches[0];
            }

            return 'href='.$matches[1].$this->previewUrl($resolved).$suffix.$matches[1];
        }, $html);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitHref(string $href): array
    {
        if (preg_match('/^([^?#]*)(.*)$/', $href, $matches) !== 1) {
            return [$href, ''];
        }

        return [$matches[1], $matches[2] ?? ''];
    }

    protected function normalizeRelativePath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/ ');

        if ($path === '') {
            return null;
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }
}
