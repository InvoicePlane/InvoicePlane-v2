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
    /*
     * Export version for CSV/Excel exports.
     * Allowed values: 1 (legacy format) or 2 (current format)
     * Can be overridden via IP_EXPORT_VERSION environment variable
     */
    'export_version' => (function () {
        $v = (int) env('IP_EXPORT_VERSION', 2);
        return in_array($v, [1, 2], true) ? $v : 2;
    })(),
];
