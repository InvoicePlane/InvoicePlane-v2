<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Modules\Core\Models\ReportBlock;
use RuntimeException;
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
     * @param array       $fields Array of field configurations
     *
     * @return void
     */
    public function saveBlockFields(ReportBlock $block, array $fields): void
    {
        try {
            // Ensure directory exists
            if ( ! Storage::disk('local')->exists('report_blocks')) {
                Storage::disk('local')->makeDirectory('report_blocks');
            }

            // Load existing config from JSON file if it exists, otherwise start fresh
            $filename = $block->filename ?: $block->slug;
            $path     = 'report_blocks/' . $filename . '.json';

            $config = [];
            if (Storage::disk('local')->exists($path)) {
                try {
                    $content = Storage::disk('local')->get($path);
                    $decoded = json_decode($content, true);

                    // Validate decoded content is an array
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $config = $decoded;
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Failed to decode existing block config, starting fresh', [
                            'path'  => $path,
                            'error' => json_last_error_msg(),
                        ]);
                    }
                } catch (Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error reading existing block config', [
                        'path'  => $path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Ensure $config is an array before assigning fields
            if ( ! is_array($config)) {
                $config = [];
            }

            $config['fields'] = $fields;

            // Save to JSON file with error handling
            try {
                Storage::disk('local')->put($path, json_encode($config, JSON_PRETTY_PRINT));
            } catch (Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to write block fields to storage', [
                    'path'  => $path,
                    'error' => $e->getMessage(),
                ]);
                throw new RuntimeException('Failed to save block fields: ' . $e->getMessage(), 0, $e);
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Unexpected error in saveBlockFields', [
                'block_id' => $block->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
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
        $path     = 'report_blocks/' . $filename . '.json';

        if ( ! Storage::disk('local')->exists($path)) {
            return [];
        }

        try {
            $content = Storage::disk('local')->get($path);
            $config  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if ( ! is_array($config)) {
                return [];
            }

            return $config['fields'] ?? [];
        } catch (JsonException $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to load block fields', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
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
        $path     = 'report_blocks/' . $filename . '.json';

        if ( ! Storage::disk('local')->exists($path)) {
            return [];
        }

        try {
            $content = Storage::disk('local')->get($path);
            $config  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return is_array($config) ? $config : [];
        } catch (JsonException $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to load block configuration', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
