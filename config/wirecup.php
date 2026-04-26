<?php

return [
    'enabled' => env('WIRECUP_ENABLED', true),

    'uri' => env('WIRECUP_URI', 'wirecup'),

    'title' => env('WIRECUP_TITLE', 'Wirecup'),

    'middleware' => ['web'],

    'root' => resource_path('wirecup'),

    'default_file' => env('WIRECUP_DEFAULT_FILE'),
];
