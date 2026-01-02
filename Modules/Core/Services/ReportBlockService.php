<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\ReportBlock;
use Throwable;

class ReportBlockService extends BaseService
{
    public function model(): string
    {
        return ReportBlock::class;
    }

    public function createReportBlock(array $data): Model
    {
        return $this->create([
            'name'         => $data['name'],
            'block_type'   => $data['block_type'],
            'slug'         => $data['slug'],
            'filename'     => $data['filename'] ?? null,
            'width'        => $data['width'],
            'data_source'  => $data['data_source'],
            'default_band' => $data['default_band'],
            'config'       => $data['config'] ?? [],
            'is_active'    => $data['is_active'] ?? true,
            'is_system'    => $data['is_system'] ?? false,
        ]);
    }

    public function updateReportBlock(ReportBlock $reportBlock, array $data): Model
    {
        $reportBlock->update([
            'name'         => $data['name'],
            'block_type'   => $data['block_type'],
            'slug'         => $data['slug'],
            'filename'     => $data['filename'] ?? null,
            'width'        => $data['width'],
            'data_source'  => $data['data_source'],
            'default_band' => $data['default_band'],
            'config'       => $data['config'] ?? [],
            'is_active'    => $data['is_active'] ?? true,
            'is_system'    => $data['is_system'] ?? false,
        ]);

        return $reportBlock;
    }

    public function deleteReportBlock(ReportBlock $reportBlock): ReportBlock
    {
        DB::beginTransaction();
        try {
            $reportBlock->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $reportBlock;
    }

    /**
     * Save block field configuration to JSON file.
     *
     * @param ReportBlock $block
     * @param array $fields Array of field configurations
     *
     * @return void
     */
    public function saveBlockFields(ReportBlock $block, array $fields): void
    {
        // Ensure directory exists
        if (!Storage::disk('local')->exists('report_blocks')) {
            Storage::disk('local')->makeDirectory('report_blocks');
        }

        // Load existing config from JSON file if it exists, otherwise start fresh
        $filename = $block->filename ?: $block->slug;
        $path = 'report_blocks/' . $filename . '.json';
        
        $config = [];
        if (Storage::disk('local')->exists($path)) {
            $content = Storage::disk('local')->get($path);
            $config = json_decode($content, true) ?? [];
        }
        
        $config['fields'] = $fields;

        // Save to JSON file
        Storage::disk('local')->put($path, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Load block field configuration from JSON file.
     *
     * @param ReportBlock $block
     *
     * @return array Array of field configurations
     */
    public function loadBlockFields(ReportBlock $block): array
    {
        $filename = $block->filename ?: $block->slug;
        $path = 'report_blocks/' . $filename . '.json';

        if (!Storage::disk('local')->exists($path)) {
            return [];
        }

        $content = Storage::disk('local')->get($path);
        $config = json_decode($content, true);

        return $config['fields'] ?? [];
    }

    /**
     * Get the full configuration for a block including fields.
     *
     * @param ReportBlock $block
     *
     * @return array
     */
    public function getBlockConfiguration(ReportBlock $block): array
    {
        $filename = $block->filename ?: $block->slug;
        $path = 'report_blocks/' . $filename . '.json';

        if (!Storage::disk('local')->exists($path)) {
            return [];
        }

        $content = Storage::disk('local')->get($path);
        $config = json_decode($content, true);

        return $config ?? [];
    }
}
