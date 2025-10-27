<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int               $id
 * @property int               $integration_id
 * @property string            $config_key
 * @property string            $config_value
 * @property PeppolIntegration $integration
 */
class PeppolIntegrationConfig extends Model
{
    public $timestamps = false;

    protected $table = 'peppol_integration_config';

    protected $guarded = [];

    /**
     * Get the PeppolIntegration that this configuration belongs to.
     *
     * @return BelongsTo the parent PeppolIntegration relation
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(PeppolIntegration::class, 'integration_id');
    }
}
