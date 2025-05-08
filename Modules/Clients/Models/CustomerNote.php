<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNote extends Model
{
    use HasFactory;

    public function client(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }
}
