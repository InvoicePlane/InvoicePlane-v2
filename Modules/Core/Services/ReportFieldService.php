<?php

namespace Modules\Core\Services;

class ReportFieldService
{
    /**
     * Get all available fields for report blocks.
     *
     * Fields are stored in a flat array structure in the config file,
     * so no nested looping is required.
     *
     * @return array
     */
    public function getAvailableFields(): array
    {
        $config = config('report-fields', []);
        $fields = [];

        // Config is already flat, just translate labels
        foreach ($config as $field) {
            $fields[] = [
                'id' => $field['id'],
                'label' => trans($field['label']),
                'source' => $field['source'],
                'format' => $field['format'] ?? null,
            ];
        }

        return $fields;
    }

    /**
     * Get fields for a specific data source.
     *
     * @param string $source
     *
     * @return array
     */
    public function getFieldsBySource(string $source): array
    {
        $config = config('report-fields', []);
        $fields = [];

        // Filter fields by source
        foreach ($config as $field) {
            if ($field['source'] === $source) {
                $fields[] = [
                    'id' => $field['id'],
                    'label' => trans($field['label']),
                    'source' => $field['source'],
                    'format' => $field['format'] ?? null,
                ];
            }
        }

        return $fields;
    }

    /**
     * Get all unique data sources from fields.
     *
     * @return array
     */
    public function getDataSources(): array
    {
        $config = config('report-fields', []);
        $sources = [];

        foreach ($config as $field) {
            if (!in_array($field['source'], $sources, true)) {
                $sources[] = $field['source'];
            }
        }

        return $sources;
    }
}
