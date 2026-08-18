<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\BelongsToCompany;

/**
 * MerchantClient — shared, encrypted credential store for external API integrations.
 *
 * Used by both Payments (payment gateways) and Peppol (e-invoicing providers).
 * Company-scoped and stores sensitive credentials (API keys, client secrets, access tokens)
 * transparently encrypted at rest via the 'encrypted' cast on merchant_value.
 *
 * @property int                           $id
 * @property int|null                      $company_id
 * @property string                        $driver provider/gateway identifier (e.g., 'stripe', 'lets_peppol')
 * @property string                        $merchant_key configuration key (e.g., 'api_key', 'client_id')
 * @property string|encrypted              $merchant_value configuration value (encrypted at rest)
 * @property string|null                   $label optional human-friendly label for disambiguation
 * @property Company|null                  $company
 */
class MerchantClient extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $table = 'merchant_clients';

    protected $guarded = [];

    protected $casts = [
        'merchant_value' => 'encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
