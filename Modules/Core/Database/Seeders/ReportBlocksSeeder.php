<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\ReportBlock;

class ReportBlocksSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'block_type'   => 'company_header',
                'name'         => 'Company Header',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => 'company',
                'default_band' => 'group_header',
                'config'       => ['show_vat_id' => true, 'show_phone' => true, 'font_size' => 10],
            ],
            [
                'block_type'   => 'client_header',
                'name'         => 'Customer Header',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => 'client',
                'default_band' => 'group_header',
                'config'       => ['show_address' => true, 'show_phone' => true, 'font_size' => 10],
            ],
            [
                'block_type'   => 'header_invoice_meta',
                'name'         => 'Invoice Metadata',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => 'invoice',
                'default_band' => 'group_header',
                'config'       => ['show_date' => true, 'show_due_date' => true, 'show_number' => true],
            ],
            [
                'block_type'   => 'invoice_items',
                'name'         => 'Invoice Items',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => 'invoice',
                'default_band' => 'details',
                'config'       => ['show_description' => true, 'show_quantity' => true, 'show_price' => true],
            ],
            [
                'block_type'   => 'invoice_item_tax',
                'name'         => 'Item Tax Details',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => 'invoice',
                'default_band' => 'details',
                'config'       => ['show_tax_name' => true, 'show_tax_rate' => true],
            ],
            [
                'block_type'   => 'footer_totals',
                'name'         => 'Invoice Totals',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => 'invoice',
                'default_band' => 'group_footer',
                'config'       => ['show_subtotal' => true, 'show_tax' => true, 'show_total' => true],
            ],
            [
                'block_type'   => 'footer_notes',
                'name'         => 'Footer Notes',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => 'invoice',
                'default_band' => 'footer',
                'config'       => ['font_size' => 9],
            ],
            [
                'block_type'   => 'footer_qr_code',
                'name'         => 'QR Code',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => 'invoice',
                'default_band' => 'footer',
                'config'       => ['size' => 100],
            ],
        ];

        foreach ($blocks as $block) {
            $filename = Str::slug($block['name']);

            ReportBlock::create([
                'is_active'    => true,
                'is_system'    => true,
                'block_type'   => $block['block_type'],
                'name'         => $block['name'],
                'slug'         => Str::slug($block['name']),
                'filename'     => $filename,
                'width'        => $block['width'],
                'data_source'  => $block['data_source'],
                'default_band' => $block['default_band'],
            ]);

            // Ensure directory exists
            if ( ! Storage::disk('local')->exists('report_blocks')) {
                Storage::disk('local')->makeDirectory('report_blocks');
            }

            // Save default config to JSON if it doesn't exist
            $path = 'report_blocks/' . $filename;
            if ( ! Storage::disk('local')->exists($path)) {
                $config           = $block['config'];
                $config['fields'] = []; // Start with no fields as requested for drag/drop
                Storage::disk('local')->put($path, json_encode($config, JSON_PRETTY_PRINT));
            }
        }
    }
}
