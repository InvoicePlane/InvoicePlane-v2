<?php

namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

/**
 * Request-scoped setting lookups for display formatting only.
 * Locale and configuration fallbacks remain the caller's responsibility.
 */
class DisplaySettings
{
    private array $values = [];

    public function get(?int $companyId, string $key): mixed
    {
        if ($companyId !== null && $value = $this->remember($companyId, $key)) {
            return $value;
        }

        return $this->remember(null, $key);
    }

    public function clear(): void
    {
        $this->values = [];
    }

    private function remember(?int $companyId, string $key): mixed
    {
        $scope = $companyId ?? 'global';

        // Cache missing values too: an unset setting must not be queried for
        // every cell, and the locale fallback still needs to resolve lazily.
        if ( ! array_key_exists($key, $this->values[$scope] ?? [])) {
            $this->values[$scope][$key] = $companyId === null
                ? Setting::getByKey($key)
                : Setting::getForCompany($companyId, $key, companyOnly: true);
        }

        return $this->values[$scope][$key];
    }
}
