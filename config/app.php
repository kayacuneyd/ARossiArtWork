<?php

return [
    'name' => 'Alexandre Mike Rossi Artworks',
    'timezone' => env('APP_TIMEZONE', 'Europe/London'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
];
