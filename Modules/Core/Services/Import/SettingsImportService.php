<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Models\Setting;

class SettingsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_settings'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['settings']);

        $this->importSettings();

        return $this->stats;
    }

    private function importSettings(): void
    {
        $settings = $this->getImportData('ip_settings');

        foreach ($settings as $v1Setting) {
            Setting::updateOrCreate(
                [
                    'company_id' => $this->companyId,
                    'key'        => $v1Setting->setting_key,
                ],
                [
                    'value' => $v1Setting->setting_value ?? '',
                ]
            );

            $this->stats['settings']++;
        }
    }
}
