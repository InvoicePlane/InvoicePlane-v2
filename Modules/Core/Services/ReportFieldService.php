<?php

namespace Modules\Core\Services;

class ReportFieldService
{
    /**
     * Get all available fields for report blocks grouped by data source.
     *
     * @return array
     */
    public function getAvailableFields(): array
    {
        $config = config('report-fields', []);
        $fields = [];

        // Flatten all fields from all data sources
        foreach ($config as $source => $sourceFields) {
            foreach ($sourceFields as $field) {
                $fields[] = [
                    'id' => $field['id'],
                    'label' => $field['label'],
                    'source' => $source,
                    'format' => $field['format'] ?? null,
                ];
            }
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

        if (!isset($config[$source])) {
            return [];
        }

        return array_map(function ($field) use ($source) {
            return [
                'id' => $field['id'],
                'label' => $field['label'],
                'source' => $source,
                'format' => $field['format'] ?? null,
            ];
        }, $config[$source]);
    }

    /**
     * Get all data sources.
     *
     * @return array
     */
    public function getDataSources(): array
    {
        return array_keys(config('report-fields', []));
    }
}
