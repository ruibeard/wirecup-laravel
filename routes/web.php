<?php

use Illuminate\Support\Facades\Route;
use Ruibeard\LaravelWirecup\Http\Controllers\WirecupController;

if (! config('wirecup.enabled', true)) {
    return;
}

Route::middleware(config('wirecup.middleware', ['web']))
    ->prefix(trim((string) config('wirecup.uri', 'wirecup'), '/'))
    ->name('wirecup.')
    ->group(function (): void {
        Route::get('/', [WirecupController::class, 'index'])->name('index');
        Route::get('/render/{path?}', [WirecupController::class, 'render'])
            ->where('path', '.*')
            ->name('preview');
    });
