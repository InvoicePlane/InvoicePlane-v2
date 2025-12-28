<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\NumberingFactory;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int              $numbering_id
 * @property int              $company_id
 * @property NumberingType    $type
 * @property string           $name
 * @property int              $next_id
 * @property int|null         $left_pad
 * @property string|null      $format
 * @property string|null      $prefix
 * @property int|null         $last_id
 * @property Company          $company
 */
class Numbering extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $table = 'numbering';

    protected $primaryKey = 'numbering_id';

    public $timestamps = false;

    protected $casts = [
        'type'     => NumberingType::class,
        'next_id'  => 'integer',
        'left_pad' => 'integer',
        'last_id'  => 'integer',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Sanitize format string by trimming whitespace.
     */
    public static function sanitizeFormat(?string $format): ?string
    {
        if ($format === null) {
            return null;
        }

        $trimmed = trim($format);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Replace the prefix placeholder in a format string.
     */
    public static function replacePrefixInFormat(
        string $format,
        ?string $newPrefix,
        ?string $oldPrefix = null
    ): string {
        if ($oldPrefix !== null && str_contains($format, $oldPrefix)) {
            $format = str_replace($oldPrefix, '{{prefix}}', $format);
        }

        if ($newPrefix !== null) {
            $format = str_replace('{{prefix}}', $newPrefix, $format);
        }

        return $format;
    }

    /*
    |--------------------------------------------------------------------------
    | Instance Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Get the resolved prefix for this numbering scheme.
     */
    public function resolvedPrefix(): string
    {
        if ($this->prefix !== null && $this->prefix !== '') {
            return $this->prefix;
        }

        return $this->type->prefix();
    }

    /**
     * Apply the format to generate a formatted number.
     */
    public function applyFormat(int $sequentialId, string $prefix): string
    {
        $format = $this->format ?? '{{prefix}}-{{number}}';

        $pad      = max((int) ($this->left_pad ?? 0), 0);
        $idPadded = str_pad((string) $sequentialId, $pad, '0', STR_PAD_LEFT);

        $replacements = [
            '{{prefix}}' => $prefix,
            '{{number}}' => $idPadded,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /*
    |--------------------------------------------------------------------------
    | Factories
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return NumberingFactory::new();
    }
}
