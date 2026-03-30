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
            // Note: Settings table doesn't have company_id in v2
            // Settings are global across the system
            Setting::updateOrCreate(
                [
                    'setting_key' => $v1Setting->setting_key,
                ],
                [
                    'setting_value' => $v1Setting->setting_value ?? '',
                ]
            );

            $this->stats['settings']++;
        }
    }
}
