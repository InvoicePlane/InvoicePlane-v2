<?php

namespace Modules\Core\Services;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBricksCollection;

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

        /*
         * page-break bricks must live at block level between the row
         * tables — dompdf ignores page-break CSS inside table cells.
         */
        $segment = [];

        foreach ($entries as $entry) {
            if (($entry['brick'] ?? null) === 'page_break') {
                $html .= $this->renderRows($segment, $data);
                $html .= (string) \Modules\Core\ReportBuilder\Bricks\PageBreakBrick::toHtml($entry['config'] ?? [], $data);
                $segment = [];

                continue;
            }

            $segment[] = $entry;
        }

        $html .= $this->renderRows($segment, $data);
        $html .= '</div>';

        return $html;
    }

    protected function renderRows(array $entries, array $data): string
    {
        $html = '';

        foreach ($this->chunkIntoRows($entries) as $row) {
            $html .= $this->renderRow($row, $data);
        }

        return $html;
    }

    /**
     * Group consecutive entries into rows on a 12-column grid — dompdf
     * cannot lay out floats reliably, so each row becomes a table.
     *
     * @return array<int, array<int, array{entry: array, width: ReportBlockWidth}>>
     */
    protected function chunkIntoRows(array $entries): array
    {
        $rows     = [];
        $row      = [];
        $rowWidth = 0;

        foreach ($entries as $entry) {
            if ( ! is_array($entry) || ReportBricksCollection::findById((string) ($entry['brick'] ?? '')) === null) {
                continue;
            }

            $width = ReportBlockWidth::tryFrom((string) ($entry['width'] ?? '')) ?? ReportBlockWidth::FULL;

            if ($row !== [] && $rowWidth + $width->getGridWidth() > 12) {
                $rows[]   = $row;
                $row      = [];
                $rowWidth = 0;
            }

            $row[] = ['entry' => $entry, 'width' => $width];
            $rowWidth += $width->getGridWidth();
        }

        if ($row !== []) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<int, array{entry: array, width: ReportBlockWidth}> $row
     */
    protected function renderRow(array $row, array $data): string
    {
        $cells = '';

        foreach ($row as $block) {
            $brickClass = ReportBricksCollection::findById((string) $block['entry']['brick']);
            $config     = is_array($block['entry']['config'] ?? null) ? $block['entry']['config'] : [];
            $inner      = (string) $brickClass::toHtml($brickClass::filterConfig($config), $data);
            $percent    = (int) round($block['width']->getGridWidth() / 12 * 100);

            $cells .= '<td class="report-block" style="width: ' . $percent . '%; vertical-align: top; padding: 0;">'
                . $inner
                . '</td>';
        }

        return '<table class="report-row" style="width: 100%; border-collapse: collapse;"><tr>' . $cells . '</tr></table>';
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
