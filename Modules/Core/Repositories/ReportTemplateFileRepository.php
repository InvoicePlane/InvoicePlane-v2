<?php

namespace Modules\Core\Repositories;

use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Repository for managing report template files.
 *
 * Report Block JSON Structure Example:
 * {
 *   "id": "block_company_header",
 *   "type": "company_header",
 *   "position": {"x": 0, "y": 0, "width": 6, "height": 4},
 *   "config": {
 *     "show_vat_id": true,
 *     "show_phone": true,
 *     "font_size": 10
 *   },
 *   "is_cloned": false,
 *   "cloned_from": null
 * }
 */
class ReportTemplateFileRepository
{
    /**
     * Save report template blocks to disk.
     *
     * @param int    $companyId
     * @param string $templateSlug
     * @param array  $blocksArray
     *
     * @return void
     */
    public function save(int $companyId, string $templateSlug, array $blocksArray): void
    {
        $path = $this->getTemplatePath($companyId, $templateSlug);
        $json = json_encode($blocksArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        Storage::disk('report_templates')->put($path, $json);
    }

    /**
     * Get report template blocks from disk.
     *
     * @param int    $companyId
     * @param string $templateSlug
     *
     * @return array
     */
    public function get(int $companyId, string $templateSlug): array
    {
        $path = $this->getTemplatePath($companyId, $templateSlug);

        if ( ! $this->exists($companyId, $templateSlug)) {
            return [];
        }

        $json = Storage::disk('report_templates')->get($path);

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }

        if ( ! is_array($decoded)) {
            return [];
        }

        // Handle grouped structure (new) vs flat array (old)
        if ($this->isGrouped($decoded)) {
            $flattened = [];
            foreach ($decoded as $bandBlocks) {
                if (is_array($bandBlocks)) {
                    foreach ($bandBlocks as $block) {
                        $flattened[] = $block;
                    }
                }
            }

            return $flattened;
        }

        return $decoded;
    }

    /**
     * Check if a report template exists.
     *
     * @param int    $companyId
     * @param string $templateSlug
     *
     * @return bool
     */
    public function exists(int $companyId, string $templateSlug): bool
    {
        $path = $this->getTemplatePath($companyId, $templateSlug);

        return Storage::disk('report_templates')->exists($path);
    }

    /**
     * Delete a report template from disk.
     *
     * @param int    $companyId
     * @param string $templateSlug
     *
     * @return bool
     */
    public function delete(int $companyId, string $templateSlug): bool
    {
        $path = $this->getTemplatePath($companyId, $templateSlug);

        if ( ! $this->exists($companyId, $templateSlug)) {
            return false;
        }

        return Storage::disk('report_templates')->delete($path);
    }

    /**
     * Get all template slugs for a company.
     *
     * @param int $companyId
     *
     * @return array
     */
    public function all(int $companyId): array
    {
        $directory = (string) $companyId;

        if ( ! Storage::disk('report_templates')->directoryExists($directory)) {
            return [];
        }

        $files = Storage::disk('report_templates')->files($directory);

        return array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);
    }

    /**
     * Check if the blocks array is grouped by band.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isGrouped(array $data): bool
    {
        // If it's an associative array and keys are known bands, it's grouped
        $bands = ['header', 'group_header', 'details', 'group_footer', 'footer'];

        foreach (array_keys($data) as $key) {
            if (in_array($key, $bands, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the full path for a template file.
     *
     * @param int    $companyId
     * @param string $templateSlug
     *
     * @return string
     */
    protected function getTemplatePath(int $companyId, string $templateSlug): string
    {
        return "{$companyId}/{$templateSlug}.json";
    }
}
