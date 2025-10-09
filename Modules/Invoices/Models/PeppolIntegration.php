<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\PeppolConnectionStatus;

/**
 * @property int $id
 * @property int $company_id
 * @property string $provider_name
 * @property string|null $encrypted_api_token
 * @property PeppolConnectionStatus $test_connection_status
 * @property string|null $test_connection_message
 * @property \Carbon\Carbon|null $test_connection_at
 * @property bool $enabled
 * @property Company $company
 * @property PeppolTransmission[] $transmissions
 * @property PeppolIntegrationConfig[] $configurations
 */
class PeppolIntegration extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $table = 'peppol_integrations';

    protected $guarded = [];

    protected $casts = [
        'test_connection_status' => PeppolConnectionStatus::class,
        'enabled' => 'boolean',
        'test_connection_at' => 'datetime',
    ];

    public function transmissions(): HasMany
    {
        return $this->hasMany(PeppolTransmission::class, 'integration_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(PeppolIntegrationConfig::class, 'integration_id');
    }

    /**
     * Get decrypted API token
     */
    public function getApiTokenAttribute(): ?string
    {
        return $this->encrypted_api_token ? decrypt($this->encrypted_api_token) : null;
    }

    /**
     * Set encrypted API token
     */
    public function setApiTokenAttribute(?string $value): void
    {
        $this->encrypted_api_token = $value ? encrypt($value) : null;
    }

    /**
     * Get configuration as array
     */
    public function getConfigAttribute(): array
    {
        return $this->configurations->pluck('config_value', 'config_key')->toArray();
    }

    /**
     * Set configuration from array
     */
    public function setConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            $this->configurations()->updateOrCreate(
                ['config_key' => $key],
                ['config_value' => $value]
            );
        }
    }

    /**
     * Get a single configuration value
     */
    public function getConfigValue(string $key, $default = null)
    {
        $config = $this->configurations()->where('config_key', $key)->first();
        return $config ? $config->config_value : $default;
    }

    /**
     * Check if connection test was successful
     */
    public function isConnectionSuccessful(): bool
    {
        return $this->test_connection_status === PeppolConnectionStatus::SUCCESS;
    }

    /**
     * Check if integration is ready to use
     */
    public function isReady(): bool
    {
        return $this->enabled && $this->isConnectionSuccessful();
    }
}
