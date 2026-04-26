<?php

namespace Ruibeard\LaravelWirecup\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ruibeard\LaravelWirecup\Wirecup;

class WirecupController extends Controller
{
    public function __construct(
        protected Wirecup $wirecup,
    ) {
    }

    public function index(Request $request)
    {
        $selected = $this->wirecup->selectedFile($request->query('file'));

        return view('wirecup::index', [
            'title' => (string) config('wirecup.title', 'Wirecup'),
            'root' => $this->wirecup->root(),
            'files' => $this->wirecup->files(),
            'selected' => $selected,
            'previewUrl' => $selected ? $this->wirecup->previewUrl($selected) : null,
        ]);
    }

    public function render(?string $path = null)
    {
        return response($this->wirecup->render($path), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
