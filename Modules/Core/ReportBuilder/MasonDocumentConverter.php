<?php

namespace Modules\Core\ReportBuilder;

use Modules\Core\Enums\ReportBlockWidth;

/**
 * Converts between bands.json entries and the Mason editor state.
 *
 * bands.json entry:  { brick: "header_company", width: "half", config: {...} }
 * mason state node:  { type: "masonBrick", attrs: { id, config, label, preview } }
 *
 * Width has no native place in a mason node, so it rides along inside the
 * config under the reserved `_width` key and is lifted back out on save.
 */
class MasonDocumentConverter
{
    public const WIDTH_KEY = '_width';

    /**
     * Band entries → mason editor state (labels and previews regenerated).
     *
     * @param array<int, array{brick: string, width: string, config: array}> $entries
     */
    public static function toMasonState(array $entries): array
    {
        $state = [];

        foreach ($entries as $entry) {
            $brickClass = ReportBricksCollection::findById((string) ($entry['brick'] ?? ''));

            if ($brickClass === null) {
                continue;
            }

            $config                  = is_array($entry['config'] ?? null) ? $entry['config'] : [];
            $config[self::WIDTH_KEY] = $entry['width'] ?? ReportBlockWidth::FULL->value;

            $state[] = [
                'type'  => 'masonBrick',
                'attrs' => [
                    'id'      => $brickClass::getId(),
                    'config'  => $config,
                    'label'   => $brickClass::getLabel(),
                    'preview' => base64_encode((string) $brickClass::toPreviewHtml($config)),
                ],
            ];
        }

        return $state;
    }

    /**
     * Mason editor state → band entries (width lifted out of config).
     *
     * @return array<int, array{brick: string, width: string, config: array}>
     */
    public static function toBandEntries(mixed $state): array
    {
        if ( ! is_array($state)) {
            return [];
        }

        if (array_key_exists('content', $state)) {
            $state = $state['content'];
        }

        $entries = [];

        foreach ($state as $node) {
            if ( ! is_array($node) || ($node['type'] ?? null) !== 'masonBrick') {
                continue;
            }

            $attrs  = $node['attrs'] ?? [];
            $config = is_array($attrs['config'] ?? null) ? $attrs['config'] : [];

            $width = ReportBlockWidth::tryFrom((string) ($config[self::WIDTH_KEY] ?? '')) ?? ReportBlockWidth::FULL;
            unset($config[self::WIDTH_KEY]);

            $entries[] = [
                'brick'  => (string) ($attrs['id'] ?? ''),
                'width'  => $width->value,
                'config' => $config,
            ];
        }

        return $entries;
    }
}
