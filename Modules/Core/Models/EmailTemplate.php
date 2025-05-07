<?php

namespace Modules\Core\Models;

use Modules\Core\Models\EmailTemplate;

use Modules\Core\Enums\EmailTemplateType;

use Modules\Core\Database\Factories\EmailTemplateFactory;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property string $title
 * @property string $type
 * @property string $subject
 * @property mixed  $body
 * @property string $from_name
 * @property string $from_email
 * @property mixed  $cc
 * @property mixed  $bcc
 */
class EmailTemplate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => EmailTemplateType::class,
    ];

    protected static function newFactory(): Factory
    {
        return EmailTemplateFactory::new();
    }
}
