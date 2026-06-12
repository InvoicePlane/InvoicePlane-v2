<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\ReportTemplate;

/**
 * Simple service for saving and loading Mason report templates.
 * No transformation needed - stores Mason JSON directly to filesystem.
 */
class MasonTemplateStorage
{
    /**
     * Save Mason editor content to filesystem.
     */
    public function save(ReportTemplate $template, string $masonJson): void
    {
        $path = $this->getTemplatePath($template);
        Storage::disk('report_templates')->put($path, $masonJson);
    }

    /**
     * Load Mason editor content from filesystem.
     */
    public function load(ReportTemplate $template): string
    {
        $path = $this->getTemplatePath($template);

        if ( ! Storage::disk('report_templates')->exists($path)) {
            return $this->getEmptyTemplate();
        }

        return Storage::disk('report_templates')->get($path);
    }

    /**
     * Check if template exists.
     */
    public function exists(ReportTemplate $template): bool
    {
        $path = $this->getTemplatePath($template);

        return Storage::disk('report_templates')->exists($path);
    }

    /**
     * Delete template.
     */
    public function delete(ReportTemplate $template): bool
    {
        $path = $this->getTemplatePath($template);

        if ( ! $this->exists($template)) {
            return false;
        }

        return Storage::disk('report_templates')->delete($path);
    }

    /**
     * Get the template file path.
     */
    protected function getTemplatePath(ReportTemplate $template): string
    {
        return "{$template->company_id}/mason_{$template->slug}.json";
    }

    /**
     * Get an empty Mason template structure.
     */
    protected function getEmptyTemplate(): string
    {
        return json_encode([
            'type'    => 'doc',
            'content' => [],
        ], JSON_PRETTY_PRINT);
    }
}
