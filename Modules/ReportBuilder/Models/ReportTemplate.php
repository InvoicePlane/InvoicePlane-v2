<?php

namespace Modules\ReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int         $id
 * @property int         $company_id
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property string      $template_type
 * @property bool        $is_system
 * @property bool        $is_active
 */
class ReportTemplate extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'template_type',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system'     => 'boolean',
        'is_active'     => 'boolean',
        'template_type' => 'string',
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
