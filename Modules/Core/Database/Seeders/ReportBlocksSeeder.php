<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockType;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportDataSource;
use Modules\Core\Models\ReportBlock;

class ReportBlocksSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'block_type'   => ReportBlockType::ADDRESS,
                'name'         => 'Company Header',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => ReportDataSource::COMPANY,
                'default_band' => ReportBand::GROUP_HEADER,
            ],
            [
                'block_type'   => ReportBlockType::ADDRESS,
                'name'         => 'Customer Header',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => ReportDataSource::CUSTOMER,
                'default_band' => ReportBand::GROUP_HEADER,
            ],
            [
                'block_type'   => ReportBlockType::METADATA,
                'name'         => 'Invoice Metadata',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::GROUP_HEADER,
            ],
            [
                'block_type'   => ReportBlockType::DETAILS,
                'name'         => 'Invoice Items',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::DETAILS,
            ],
            [
                'block_type'   => ReportBlockType::DETAILS,
                'name'         => 'Item Tax Details',
                'width'        => ReportBlockWidth::FULL,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::DETAILS,
                'config'       => ['show_tax_name' => true, 'show_tax_rate' => true],
            ],
            [
                'block_type'   => ReportBlockType::TOTALS,
                'name'         => 'Invoice Totals',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::GROUP_FOOTER,
            ],
            [
                'block_type'   => ReportBlockType::METADATA,
                'name'         => 'Footer Notes',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::FOOTER,
            ],
            [
                'block_type'   => ReportBlockType::METADATA,
                'name'         => 'QR Code',
                'width'        => ReportBlockWidth::HALF,
                'data_source'  => ReportDataSource::INVOICE,
                'default_band' => ReportBand::FOOTER,
            ],
        ];

        foreach ($blocks as $block) {
            $baseSlug = Str::slug($block['name']);
            $slug     = $baseSlug . '-' . Str::random(8);
            $filename = $slug;

            ReportBlock::create([
                'is_active'    => true,
                'is_system'    => true,
                'block_type'   => $block['block_type'],
                'name'         => $block['name'],
                'slug'         => $slug,
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
            $path = 'report_blocks/' . $filename . '.json';
            if ( ! Storage::disk('local')->exists($path)) {
                $config           = $block['config'];
                $config['fields'] = []; // Start with no fields as requested for drag/drop
                Storage::disk('local')->put($path, json_encode($config, JSON_PRETTY_PRINT));
            }
        }
    }
}
