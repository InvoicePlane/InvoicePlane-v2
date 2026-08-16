<?php

return [
    'date_formats' => [
        'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
        'd-m-Y' => date('d-m-Y') . ' (d-m-Y)',
        'd.M.Y' => date('d.M.Y') . ' (d.M.Y)',
        'j/n/Y' => date('j/n/Y') . ' (j/n/Y)',
        'd M,Y' => date('d M,Y') . ' (d M,Y)',
        'm/d/Y' => date('m/d/Y') . ' (m/d/Y)',
        'm-d-Y' => date('m-d-Y') . ' (m-d-Y)',
        'm.d.Y' => date('m.d.Y') . ' (m.d.Y)',
        'Y/m/d' => date('Y/m/d') . ' (Y/m/d)',
        'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
        'Y.m.d' => date('Y.m.d') . ' (Y.m.d)',
    ],
    'default_decimals_for_items' => [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
    ],
    'number_of_items_in_list' => [
        '15'  => '15',  // <<== for legacy purposes
        '25'  => '25',
        '50'  => '50',
        '100' => '100',
        '250' => '250',
    ],
    'tax_rate_decimal_places' => [
        '2' => '2',
        '3' => '3',
    ],
    'export_version' => 2,

    /*
     * PDF rendering — driver class name under Modules\Core\Support\PDF\Drivers.
     */
    'pdfDriver'        => env('IP_PDF_DRIVER', 'domPDF'),
    'paperSize'        => env('IP_PDF_PAPER_SIZE', 'a4'),
    'paperOrientation' => env('IP_PDF_PAPER_ORIENTATION', 'portrait'),

    // Only used when IP_PDF_DRIVER=Browsershot (headless Chromium; needs Node + Puppeteer)
    'browsershot' => [
        'node_binary' => env('IP_BROWSERSHOT_NODE_BINARY'),
        'npm_binary'  => env('IP_BROWSERSHOT_NPM_BINARY'),
        'chrome_path' => env('IP_BROWSERSHOT_CHROME_PATH'),
        'no_sandbox'  => env('IP_BROWSERSHOT_NO_SANDBOX', false),
    ],
];
