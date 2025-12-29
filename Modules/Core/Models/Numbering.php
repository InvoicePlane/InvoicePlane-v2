<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\NumberingFactory;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

/**
 * @property int                  $id
 * @property int                  $company_id
 * @property NumberingType        $type
 * @property string               $name
 * @property string|null          $group_identifier_format
 * @property int                  $next_id
 * @property int                  $left_pad
 * @property string|null          $format
 * @property string|null          $prefix
 * @property int                  $reset_number
 * @property int                  $last_id
 * @property int                  $last_year
 * @property int                  $last_month
 * @property int                  $last_week
 * @property Company              $company
 * @property Collection|Invoice[] $invoices
 * @property Collection|Quote[]   $quotes
 */
class Numbering extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $table = 'numbering';

    public $timestamps = false;

    protected $casts = [
        'type'         => NumberingType::class,
        'next_id'      => 'integer',
        'left_pad'     => 'integer',
        'reset_number' => 'integer',
        'last_id'      => 'integer',
        'last_year'    => 'integer',
        'last_month'   => 'integer',
        'last_week'    => 'integer',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Return all the "template–insertion" tags you want
     * to offer in your form dropdown.
     */
    public static function availableTags(): array
    {
        return [
            '{{yy}}'     => trans('ip.year_short'),    // e.g. "23"
            '{{year}}'   => trans('ip.year_full'),     // e.g. "2023"
            '{{month}}'  => trans('ip.month'),         // e.g. "04"
            '{{day}}'    => trans('ip.day'),           // e.g. "27"
            '{{id}}'     => trans('ip.id'),            // e.g. "42"
            '{{prefix}}' => trans('ip.prefix'),        // e.g. "INV"
            '{{number}}' => trans('ip.number'),        // e.g. "0001"
        ];
    }

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

    /**
     * Find numbering ID by name.
     *
     * @param string $name
     *
     * @return int|null
     */
    public static function findIdByName(string $name): ?int
    {
        if ($group = self::query()->where('name', $name)->first()) {
            return $group->id;
        }

        return null;
    }

    public static function getList()
    {
        return self::orderBy('name')->pluck('name', 'id')->all();
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
        $idPadded = mb_str_pad((string) $sequentialId, $pad, '0', STR_PAD_LEFT);

        $replacements = [
            '{{prefix}}' => $prefix,
            '{{number}}' => $idPadded,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'numbering_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'numbering_id');
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
