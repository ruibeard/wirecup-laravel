<?php

return [
    'enabled' => env('WIRECUP_ENABLED', true),

    'uri' => env('WIRECUP_URI', 'wirecup'),

    'title' => env('WIRECUP_TITLE', 'Wirecup'),

    'middleware' => ['web'],

    'root' => base_path('.agents/.cup'),

    'default_file' => env('WIRECUP_DEFAULT_FILE'),
];
