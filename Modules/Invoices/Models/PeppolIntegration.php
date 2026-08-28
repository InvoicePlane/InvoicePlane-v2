<?php

namespace Modules\Invoices\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Models\MerchantClient;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\PeppolConnectionStatus;

/**
 * @property int                       $id
 * @property int                       $company_id
 * @property string                    $provider_name
 * @property PeppolConnectionStatus    $test_connection_status
 * @property string|null               $test_connection_message
 * @property CarbonInterface|null      $test_connection_at
 * @property bool                      $enabled
 * @property Company                   $company
 * @property PeppolTransmission[]      $transmissions
 * @property MerchantClient[]          $configurations
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

    protected static function booted(): void
    {
        // Do not apply global company scope to this model during Filament admin access,
        // where an admin managing integrations across companies should see all of them.
        // This is a shared cross-company registry, not company-scoped data.
        if (app()->runningInConsole()) {
            static::addGlobalScope('skip_company_scope', function ($query): void {
                // In console (migrations, commands), skip the global scope entirely
            });
        }
    }

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
     * Credentials are now stored in the shared merchant_clients table, scoped by company and provider.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany relation to MerchantClient models
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(MerchantClient::class, 'company_id', 'company_id')
            ->where('driver', $this->provider_name);
    }

    /**
     * Provide integration configurations as an associative array keyed by configuration keys.
     *
     * Maps merchant_clients rows (from the shared credential table) to merchant_key/merchant_value pairs.
     *
     * @return array associative array mapping credential keys (merchant_key) to their values (merchant_value)
     */
    public function getConfigAttribute(): array
    {
        return collect($this->configurations)->pluck('merchant_value', 'merchant_key')->toArray();
    }

    /**
     * Upserts integration configuration entries from an associative array.
     *
     * Each array key is saved as `merchant_key` and its corresponding value as `merchant_value`
     * on the related merchant_clients rows; existing entries are updated and missing ones created.
     *
     * @param array $config associative array of configuration entries where keys are configuration keys and values are configuration values
     */
    public function setConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            MerchantClient::updateOrCreate(
                [
                    'company_id'    => $this->company_id,
                    'driver'        => $this->provider_name,
                    'merchant_key'  => $key,
                ],
                ['merchant_value' => $value]
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
        $config = MerchantClient::where('company_id', $this->company_id)
            ->where('driver', $this->provider_name)
            ->where('merchant_key', $key)
            ->first();

        return $config ? $config->merchant_value : $default;
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
