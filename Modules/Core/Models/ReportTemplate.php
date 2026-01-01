<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                $id
 * @property int                $company_id
 * @property string             $name
 * @property string             $slug
 * @property string|null        $description
 * @property ReportTemplateType $template_type
 * @property bool               $is_system
 * @property bool               $is_active
 */
class ReportTemplate extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'is_system'     => 'boolean',
        'is_active'     => 'boolean',
        'template_type' => ReportTemplateType::class,
    ];

    /**
     * Check if the template can be cloned.
     */
    public function isCloneable(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the template is a system template.
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Get the file path for the template.
     */
    public function getFilePath(): string
    {
        return "{$this->company_id}/{$this->slug}.json";
    }
}
