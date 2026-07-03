<?php

declare(strict_types=1);

return [
    'token'   => env('GITHUB_TOKEN'),
    'owner'   => env('GITHUB_OWNER', 'invoiceplane'),
    'repo'    => env('GITHUB_REPO', 'invoiceplane'),
    'timeout' => 30,
    'retries' => 3,
];
