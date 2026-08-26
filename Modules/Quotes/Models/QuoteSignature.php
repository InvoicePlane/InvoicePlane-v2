<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Quotes\Database\Factories\QuoteSignatureFactory;

/**
 * @property int          $id
 * @property int          $company_id
 * @property int          $quote_id
 * @property int|null     $user_id
 * @property string       $signer_name
 * @property string       $signature_disk
 * @property string       $signature_path
 * @property Carbon       $signed_at
 * @property string|null  $ip_address
 * @property string|null  $user_agent
 * @property Quote        $quote
 * @property User|null    $user
 */
class QuoteSignature extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected $guarded = [];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {
        return QuoteSignatureFactory::new();
    }
}
