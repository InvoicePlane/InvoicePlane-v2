<?php

declare(strict_types=1);

return [
    'max_concurrency' => (int) env('FABLE5_MAX_CONCURRENCY', 4),
    'storage_path' => storage_path('fable5'),
];
