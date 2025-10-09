<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $provider_name
 * @property string|null $encrypted_api_token
 * @property array|null $config
 * @property string $test_connection_status
 * @property string|null $test_connection_message
 * @property \Carbon\Carbon|null $test_connection_at
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Company $company
 * @property PeppolTransmission[] $transmissions
 */
class PeppolIntegration extends Model
{
    use BelongsToCompany;

    protected $table = 'peppol_integrations';

    protected $fillable = [
        'company_id',
        'provider_name',
        'encrypted_api_token',
        'config',
        'test_connection_status',
        'test_connection_message',
        'test_connection_at',
        'enabled',
    ];

    protected $casts = [
        'config' => 'array',
        'enabled' => 'boolean',
        'test_connection_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transmissions(): HasMany
    {
        return $this->hasMany(PeppolTransmission::class, 'integration_id');
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
     * Check if connection test was successful
     */
    public function isConnectionSuccessful(): bool
    {
        return $this->test_connection_status === 'success';
    }

    /**
     * Check if integration is ready to use
     */
    public function isReady(): bool
    {
        return $this->enabled && $this->isConnectionSuccessful();
    }
}
