<?php

namespace Modules\ReportBuilder\Repositories;

use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Repository for managing report template files.
 *
 * Report Block JSON Structure Example:
 * {
 *   "id": "block_header_company",
 *   "type": "header_company",
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

        return is_array($decoded) ? $decoded : [];
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
