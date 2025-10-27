<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\PeppolConnectionStatus;

/**
 * @property int                       $id
 * @property int                       $company_id
 * @property string                    $provider_name
 * @property string|null               $encrypted_api_token
 * @property PeppolConnectionStatus    $test_connection_status
 * @property string|null               $test_connection_message
 * @property \Carbon\Carbon|null       $test_connection_at
 * @property bool                      $enabled
 * @property Company                   $company
 * @property PeppolTransmission[]      $transmissions
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
        'enabled'                => 'boolean',
        'test_connection_at'     => 'datetime',
    ];

    /**
     * Get the transmissions associated with this integration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany a has-many relation for PeppolTransmission models keyed by `integration_id`
     */
    public function transmissions(): HasMany
    {
        return $this->hasMany(PeppolTransmission::class, 'integration_id');
    }

    /**
     * Get the Eloquent relation for this integration's configuration entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany relation to PeppolIntegrationConfig models keyed by `integration_id`
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(PeppolIntegrationConfig::class, 'integration_id');
    }

    /**
     * Return the decrypted API token for the integration.
     *
     * @return string|null the decrypted API token, or null if no token is stored
     */
    public function getApiTokenAttribute(): ?string
    {
        return $this->encrypted_api_token ? decrypt($this->encrypted_api_token) : null;
    }

    /**
     * Store the API token on the model in encrypted form.
     *
     * If `$value` is null the stored encrypted token will be set to null.
     *
     * @param string|null $value the plaintext API token to encrypt and store, or null to clear it
     */
    public function setApiTokenAttribute(?string $value): void
    {
        $this->encrypted_api_token = $value ? encrypt($value) : null;
    }

    /**
     * Provide integration configurations as an associative array keyed by configuration keys.
     *
     * @return array associative array mapping configuration keys (`config_key`) to their values (`config_value`)
     */
    public function getConfigAttribute(): array
    {
        return $this->configurations->pluck('config_value', 'config_key')->toArray();
    }

    /**
     * Upserts integration configuration entries from an associative array.
     *
     * Each array key is saved as `config_key` and its corresponding value as `config_value`
     * on the related configurations; existing entries are updated and missing ones created.
     *
     * @param array $config associative array of configuration entries where keys are configuration keys and values are configuration values
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
     * Retrieve a configuration value for the given key from this integration's configurations.
     *
     * @param string $key     the configuration key to look up
     * @param mixed  $default value to return if the configuration key does not exist
     *
     * @return mixed the configuration value if found, otherwise the provided default
     */
    public function getConfigValue(string $key, $default = null)
    {
        $config = $this->configurations()->where('config_key', $key)->first();

        return $config ? $config->config_value : $default;
    }

    /**
     * Determine whether the last connection test succeeded.
     *
     * @return bool `true` if `test_connection_status` equals PeppolConnectionStatus::SUCCESS, `false` otherwise
     */
    public function isConnectionSuccessful(): bool
    {
        return $this->test_connection_status === PeppolConnectionStatus::SUCCESS;
    }

    /**
     * Determine whether the integration is ready for use.
     *
     * Integration is considered ready when it is enabled and the connection check is successful.
     *
     * @return bool `true` if the integration is enabled and the connection is successful, `false` otherwise
     */
    public function isReady(): bool
    {
        return $this->enabled && $this->isConnectionSuccessful();
    }
}
