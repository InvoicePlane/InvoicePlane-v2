<?php

namespace Modules\Core\Services;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Mason\ReportBricksCollection;

/**
 * Renders a report template (manifest + bands) with entity data into the
 * HTML document handed to the PDF driver. Bands render in document order;
 * a band with "keep_together" enabled in the manifest's band_options is
 * wrapped so it never breaks across pages.
 */
class ReportRenderer
{
    /**
     * @param array{manifest: array, bands: array<string, array>} $template
     */
    public function render(array $template, array $data): string
    {
        $body = '';

        foreach (ReportBand::ordered() as $band) {
            $body .= $this->renderBand($band, $template, $data);
        }

        return $this->wrapDocument($body, (string) ($template['manifest']['name'] ?? 'Report'));
    }

    protected function renderBand(ReportBand $band, array $template, array $data): string
    {
        $entries = $template['bands'][$band->value] ?? [];

        if ($entries === []) {
            return '';
        }

        $style = 'width: 100%;';

        if ($this->keepsTogether($band, $template['manifest'])) {
            $style .= ' page-break-inside: avoid;';
        }

        $html = '<div class="report-band report-band-' . $band->value . '" style="' . $style . '">';

        foreach ($entries as $entry) {
            $html .= $this->renderEntry($entry, $data);
        }

        $html .= '<div style="clear: both;"></div></div>';

        return $html;
    }

    protected function renderEntry(array $entry, array $data): string
    {
        $brickClass = ReportBricksCollection::findById((string) ($entry['brick'] ?? ''));

        if ($brickClass === null) {
            return '';
        }

        $config = is_array($entry['config'] ?? null) ? $entry['config'] : [];
        $width  = ReportBlockWidth::tryFrom((string) ($entry['width'] ?? '')) ?? ReportBlockWidth::FULL;
        $inner  = (string) $brickClass::toHtml($brickClass::filterConfig($config), $data);

        $percent = (int) round($width->getGridWidth() / 12 * 100);

        return '<div class="report-block" style="float: left; width: ' . $percent . '%; box-sizing: border-box;">'
            . $inner
            . '</div>';
    }

    protected function keepsTogether(ReportBand $band, array $manifest): bool
    {
        return (bool) ($manifest['band_options'][$band->value]['keep_together'] ?? false);
    }

    protected function wrapDocument(string $body, string $title): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>{$this->escape($title)}</title>
                <style>
                    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #111; margin: 0; }
                    table { border-collapse: collapse; width: 100%; }
                    .report-band { overflow: hidden; }
                </style>
            </head>
            <body>
            {$body}
            </body>
            </html>
            HTML;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
