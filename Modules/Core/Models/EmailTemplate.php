<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\EmailTemplateFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $table = 'email_templates';

    public $timestamps = false;

    public $filterable = [
        'email_template_title',
    ];

    public $orderable = [
        'email_template_title',
    ];

    protected $primaryKey = 'email_template_id';

    protected $fillable = [
        'email_template_title',
        'email_template_type',
        'email_template_body',
        'email_template_subject',
        'email_template_from_name',
        'email_template_from_email',
        'email_template_cc',
        'email_template_bcc',
        'email_template_pdf_template',
    ];

    protected static function newFactory(): Factory
    {
        return EmailTemplateFactory::new();
    }
}
