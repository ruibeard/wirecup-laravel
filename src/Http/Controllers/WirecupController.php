<?php

namespace Ruibeard\WirecupLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ruibeard\WirecupLaravel\WirecupRenderer;

class WirecupController extends Controller
{
    public function __construct(private WirecupRenderer $renderer) {}

    public function index(Request $request)
    {
        $files = $this->cupFiles();
        $selected = $request->query('file');

        if ($selected === null || ! in_array($selected, $files, true)) {
            $selected = $files[0] ?? null;
        }

        return view('wirecup::index', [
            'title'      => config('wirecup.title', 'Wirecup'),
            'files'      => $files,
            'selected'   => $selected,
            'previewUrl' => $selected ? $this->previewUrl($selected) : null,
        ]);
    }

    public function render(string $file = '')
    {
        $path = $this->safePath($file);

        abort_unless($path !== null, 404);

        $html = $this->renderer->render(file_get_contents($path), basename($file, '.cup'));

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // -------------------------------------------------------------------------

    private function cupFiles(): array
    {
        $root = config('wirecup.root');

        if (! is_dir($root)) {
            return [];
        }

        $files = array_values(array_map(
            fn ($f) => basename($f),
            glob($root.'/*.cup') ?: [],
        ));

        sort($files);

        return $files;
    }

    private function previewUrl(string $file): string
    {
        return route('wirecup.render', ['file' => $file]);
    }

    private function safePath(string $file): ?string
    {
        $root = realpath(config('wirecup.root'));

        if ($root === false) {
            return null;
        }

        // only allow simple filenames — no slashes, must end in .cup
        if (str_contains($file, '/') || str_contains($file, '\\') || ! str_ends_with($file, '.cup')) {
            return null;
        }

        $path = $root.DIRECTORY_SEPARATOR.$file;

        return is_file($path) ? $path : null;
    }
}
