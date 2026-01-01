<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\ReportBlock;

class ReportBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = [
            [
                'block_type' => 'company_header',
                'name'       => 'Company Header',
                'width'      => ReportBlockWidth::HALF,
            ],
            [
                'block_type' => 'client_header',
                'name'       => 'Customer Header',
                'width'      => ReportBlockWidth::HALF,
            ],
            [
                'block_type' => 'header_invoice_meta',
                'name'       => 'Invoice Metadata',
                'width'      => ReportBlockWidth::FULL,
            ],
            [
                'block_type' => 'invoice_items',
                'name'       => 'Invoice Items',
                'width'      => ReportBlockWidth::FULL,
            ],
            [
                'block_type' => 'invoice_item_tax',
                'name'       => 'Item Tax Details',
                'width'      => ReportBlockWidth::FULL,
            ],
            [
                'block_type' => 'footer_totals',
                'name'       => 'Invoice Totals',
                'width'      => ReportBlockWidth::HALF,
            ],
            [
                'block_type' => 'footer_notes',
                'name'       => 'Footer Notes',
                'width'      => ReportBlockWidth::HALF,
            ],
            [
                'block_type' => 'footer_qr_code',
                'name'       => 'QR Code',
                'width'      => ReportBlockWidth::HALF,
            ],
        ];

        foreach ($blocks as $block) {
            ReportBlock::create([
                'is_active'  => true,
                'is_system'  => true,
                'block_type' => $block['block_type'],
                'name'       => $block['name'],
                'slug'       => Str::slug($block['name']),
                'filename'   => Str::slug($block['name']) . '.json',
                'width'      => $block['width'],
            ]);
        }
    }
}
