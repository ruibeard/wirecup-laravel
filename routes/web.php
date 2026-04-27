<?php

use Illuminate\Support\Facades\Route;
use Ruibeard\WirecupLaravel\Http\Controllers\WirecupController;

if (! config('wirecup.enabled', true)) {
    return;
}

Route::middleware(config('wirecup.middleware', ['web']))
    ->prefix(trim(config('wirecup.uri', 'wirecup'), '/'))
    ->group(function () {
        Route::get('/', [WirecupController::class, 'index'])->name('wirecup.index');
        Route::get('/render/{file}', [WirecupController::class, 'render'])->name('wirecup.render');
    });
