<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\NoteTemplateFactory;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int    $id
 * @property int    $company_id
 * @property string $template_title
 * @property string $template_body
 * @property Company $company
 */
class NoteTemplate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return NoteTemplateFactory::new();
    }
}
