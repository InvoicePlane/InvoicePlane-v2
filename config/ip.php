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

    /*
     * Report builder resource ceilings. These bound the work a single tenant
     * can force onto a shared render worker.
     *
     *  - queue: when true, "Download PDF" renders in a queued job and stores
     *    the file instead of rendering inline in the web request. Off by
     *    default (the download is a direct response).
     *  - max_rows: line items rendered per detail brick before truncation.
     *  - max_bricks_per_band: bricks kept per band when a template is saved.
     *  - max_template_bytes: rejected on save above this encoded size.
     *  - render_time_limit: set_time_limit() guard around an inline render.
     */
    'report' => [
        'queue'               => (bool) env('IP_REPORT_QUEUE', false),
        'max_rows'            => (int) env('IP_REPORT_MAX_ROWS', 2000),
        'max_bricks_per_band' => (int) env('IP_REPORT_MAX_BRICKS_PER_BAND', 50),
        'max_template_bytes'  => (int) env('IP_REPORT_MAX_TEMPLATE_BYTES', 262144),
        'render_time_limit'   => (int) env('IP_REPORT_RENDER_TIME_LIMIT', 120),
    ],
];
