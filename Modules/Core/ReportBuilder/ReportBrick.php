<?php

namespace Modules\Core\ReportBuilder;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportTemplateType;
use ReflectionProperty;

/**
 * Base class for all report bricks.
 *
 * Adds band placement rules and config-schema introspection on top of the
 * Mason Brick contract. Bricks remain pure code — their configuration is
 * plain data validated against the keys declared in configureBrickAction().
 */
abstract class ReportBrick extends Brick
{
    /**
     * Cached config keys per brick class.
     *
     * @var array<class-string, array<string>>
     */
    protected static array $configKeysCache = [];

    /**
     * The bands this brick may be placed in.
     *
     * Defaults are inferred from the class name prefix (Header, Detail, Footer);
     * override for bricks that do not follow the prefix convention.
     *
     * @return array<ReportBand>
     */
    public static function allowedBands(): array
    {
        $basename = class_basename(static::class);

        return match (true) {
            str_starts_with($basename, 'Header') => [ReportBand::HEADER, ReportBand::GROUP_HEADER],
            str_starts_with($basename, 'Detail') => [ReportBand::DETAILS],
            str_starts_with($basename, 'Footer') => [ReportBand::GROUP_FOOTER, ReportBand::FOOTER],
            default                              => ReportBand::cases(),
        };
    }

    /**
     * The document types this brick may be offered for. Defaults to every
     * type; override for bricks whose data only exists on one document type
     * (e.g. an invoice-only metadata brick has nothing to render on a quote).
     *
     * @return array<ReportTemplateType>
     */
    public static function allowedTypes(): array
    {
        return ReportTemplateType::cases();
    }

    /**
     * The config keys this brick accepts, derived from its configure action
     * schema. Used to filter persisted config against the brick's own schema.
     *
     * @return array<string>
     */
    public static function configKeys(): array
    {
        if (isset(static::$configKeysCache[static::class])) {
            return static::$configKeysCache[static::class];
        }

        $action = static::configureBrickAction(Action::make('configure'));

        $property = new ReflectionProperty(Action::class, 'schema');
        $schema   = $property->getValue($action);

        $keys = [];

        if (is_array($schema)) {
            foreach ($schema as $component) {
                if (method_exists($component, 'getName')) {
                    $keys[] = $component->getName();
                }
            }
        }

        return static::$configKeysCache[static::class] = $keys;
    }

    /**
     * Filter a persisted config array down to the keys this brick declares,
     * then coerce the well-known presentational values to safe shapes.
     */
    public static function filterConfig(array $config): array
    {
        return static::coerceConfigValues(
            array_intersect_key($config, array_flip(static::configKeys())),
        );
    }

    /**
     * The Filament configure form validates numeric/enum fields client-side
     * only. A crafted Livewire payload or a hand-edited template JSON can
     * still put arbitrary strings on keys that end up inside a style=""
     * attribute, so the presentational keys are coerced (or dropped) here —
     * on every save and every render.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected static function coerceConfigValues(array $config): array
    {
        foreach ($config as $key => $value) {
            if ($key === 'font_size' || str_ends_with((string) $key, '_font_size')) {
                $config[$key] = max(4, min(96, (int) $value));
            }
        }

        $enums = [
            'text_align'            => ['left', 'center', 'right', 'justify'],
            'font_weight'           => ['normal', 'bold', 'bolder', 'lighter'],
            'font_style'            => ['normal', 'italic'],
            'description_placement' => ['inline_column', 'below_row', 'hidden'],
        ];

        foreach ($enums as $key => $allowed) {
            if (array_key_exists($key, $config) && ! in_array($config[$key], $allowed, true)) {
                unset($config[$key]);
            }
        }

        return $config;
    }
}
