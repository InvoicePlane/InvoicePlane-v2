<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $transmission_id
 * @property string $response_key
 * @property string $response_value
 * @property PeppolTransmission $transmission
 */
class PeppolTransmissionResponse extends Model
{
    public $timestamps = false;

    protected $table = 'peppol_transmission_responses';

    protected $guarded = [];

    public function transmission(): BelongsTo
    {
        return $this->belongsTo(PeppolTransmission::class, 'transmission_id');
    }
}
