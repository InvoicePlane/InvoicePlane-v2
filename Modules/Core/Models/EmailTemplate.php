<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\EmailTemplateFactory;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                 $id
 * @property int                 $company_id
 * @property string|null         $title
 * @property EmailTemplateType|null $type
 * @property string              $body
 * @property string|null $subject
 * @property string|null $from_name
 * @property string|null $from_email
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $pdf_template
 * @property Company     $company
 */
class EmailTemplate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'type' => EmailTemplateType::class,
    ];

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return EmailTemplateFactory::new();
    }
}
