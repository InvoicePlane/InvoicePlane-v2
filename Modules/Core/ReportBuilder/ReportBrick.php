<?php

namespace Modules\Core\ReportBuilder;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Modules\Core\Enums\ReportBand;
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
     * Filter a persisted config array down to the keys this brick declares.
     */
    public static function filterConfig(array $config): array
    {
        return array_intersect_key($config, array_flip(static::configKeys()));
    }
}
