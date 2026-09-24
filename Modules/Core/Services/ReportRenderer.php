<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportGroupBy;
use Modules\Core\ReportBuilder\ReportBricksCollection;
use Throwable;

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
        $manifest = $template['manifest'] ?? [];
        $groupBy  = $this->groupBy($manifest);

        if ($groupBy === null) {
            $body = '';

            foreach (ReportBand::ordered() as $band) {
                $body .= $this->renderBand($band, $template, $data);
            }

            return $this->wrapDocument($body, (string) ($manifest['name'] ?? 'Report'));
        }

        $body = $this->renderGroupedDocument($template, $data, $groupBy);

        return $this->wrapDocument($body, (string) ($manifest['name'] ?? 'Report'));
    }

    /**
     * Render band entries for the builder's live preview. Same band iteration,
     * row packing and per-brick isolation as render(), but each brick shows
     * its toPreviewHtml() (no entity data) and there is no document wrapper —
     * so the preview and the print output can never drift apart on layout.
     *
     * @param array<string, array<int, array{brick: string, width: string, config: array}>> $bands
     */
    public function renderPreview(array $bands): string
    {
        $template = ['manifest' => [], 'bands' => $bands];
        $body     = '';

        foreach (ReportBand::ordered() as $band) {
            $body .= $this->renderBand($band, $template, [], preview: true);
        }

        return $body;
    }

    protected function renderGroupedDocument(array $template, array $data, ReportGroupBy $groupBy): string
    {
        $body = '';

        // Document-level header
        $body .= $this->renderBand(ReportBand::HEADER, $template, $data);

        // Partition items into groups preserving first-seen order
        $groupKey = $groupBy->value;
        $groups   = $this->extractGroupKeys($data, $groupKey);

        if ($groups === []) {
            $groupHtml = $this->renderBand(ReportBand::GROUP_HEADER, $template, $data)
                . $this->renderBand(ReportBand::DETAILS, $template, $data)
                . $this->renderBand(ReportBand::GROUP_FOOTER, $template, $data);

            if ($groupHtml !== '') {
                $style = $this->keepsGroupTogether($template['manifest'] ?? []) ? ' style="page-break-inside: avoid;"' : '';
                $body .= '<div class="report-group"' . $style . '>' . $groupHtml . '</div>';
            }
        } else {
            foreach ($groups as $groupValue) {
                $groupData = $this->buildGroupData($data, $groupKey, $groupValue);

                $groupHtml = $this->renderBand(ReportBand::GROUP_HEADER, $template, $groupData)
                    . $this->renderBand(ReportBand::DETAILS, $template, $groupData)
                    . $this->renderBand(ReportBand::GROUP_FOOTER, $template, $groupData);

                if ($groupHtml !== '') {
                    $style = $this->keepsGroupTogether($template['manifest'] ?? []) ? ' style="page-break-inside: avoid;"' : '';
                    $body .= '<div class="report-group"' . $style . '>' . $groupHtml . '</div>';
                }
            }
        }

        // Document-level footer
        $body .= $this->renderBand(ReportBand::FOOTER, $template, $data);

        return $body;
    }

    /**
     * @return array<int, string>
     */
    protected function extractGroupKeys(array $data, string $groupKey): array
    {
        $seen = [];

        $collections = [
            $data['items'] ?? [],
            $data['invoice_items'] ?? [],
            $data['quote_items'] ?? [],
            $data['expense_items'] ?? [],
        ];

        foreach ($collections as $collection) {
            if ( ! is_array($collection)) {
                continue;
            }

            foreach ($collection as $item) {
                if ( ! is_array($item)) {
                    continue;
                }

                $val = (string) ($item[$groupKey] ?? '');

                if ( ! in_array($val, $seen, true)) {
                    $seen[] = $val;
                }
            }
        }

        return $seen;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function buildGroupData(array $data, string $groupKey, string $groupValue): array
    {
        $groupData = $data;

        $filter = fn (mixed $collection): array => is_array($collection)
            ? array_values(array_filter($collection, fn ($item): bool => is_array($item) && (string) ($item[$groupKey] ?? '') === $groupValue))
            : [];

        $groupItems        = $filter($data['items'] ?? []);
        $groupInvoiceItems = $filter($data['invoice_items'] ?? []);
        $groupQuoteItems   = $filter($data['quote_items'] ?? []);
        $groupExpenseItems = $filter($data['expense_items'] ?? []);

        $groupData['items']         = $groupItems;
        $groupData['invoice_items'] = $groupInvoiceItems;
        $groupData['quote_items']   = $groupQuoteItems;
        $groupData['expense_items'] = $groupExpenseItems;

        $label = $groupValue !== '' ? $groupValue : trans('ip.none');

        $groupData['group'] = [
            'field' => $groupKey,
            'key'   => $groupValue,
            'name'  => $label,
            'label' => $label,
            'value' => $groupValue,
        ];

        $itemsToSum = $groupItems !== []
            ? $groupItems
            : ($groupInvoiceItems !== [] ? $groupInvoiceItems : ($groupQuoteItems !== [] ? $groupQuoteItems : $groupExpenseItems));

        $groupTotals = $this->calculateGroupTotals($itemsToSum);

        $groupData['group_totals']    = $groupTotals;
        $groupData['document_totals'] = $data['totals'] ?? [];

        return $groupData;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<string, string>
     */
    protected function calculateGroupTotals(array $items): array
    {
        $subtotal = 0.0;
        $tax      = 0.0;
        $total    = 0.0;

        foreach ($items as $item) {
            $itemTax   = (float) ($item['tax'] ?? 0);
            $itemTotal = (float) ($item['total'] ?? ($item['amount'] ?? 0));
            $tax += $itemTax;
            $total += $itemTotal;

            if (isset($item['subtotal'])) {
                $subtotal += (float) $item['subtotal'];
            } elseif (isset($item['unit_price'])) {
                $subtotal += ((float) ($item['quantity'] ?? 1)) * (float) $item['unit_price'];
            } elseif (isset($item['price'])) {
                $subtotal += ((float) ($item['quantity'] ?? 1)) * (float) $item['price'];
            } elseif (isset($item['amount'])) {
                $subtotal += (float) $item['amount'];
            } else {
                $subtotal += $itemTotal - $itemTax;
            }
        }

        return [
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax'      => number_format($tax, 2, '.', ''),
            'total'    => number_format($total, 2, '.', ''),
        ];
    }

    protected function groupBy(array $manifest): ?ReportGroupBy
    {
        $value = $manifest['band_options'][ReportBand::DETAILS->value]['group_by'] ?? null;

        if ($value instanceof ReportGroupBy) {
            return $value;
        }

        return is_string($value) ? ReportGroupBy::tryFrom($value) : null;
    }

    protected function keepsGroupTogether(array $manifest): bool
    {
        return (bool) ($manifest['band_options']['group']['keep_together']
            ?? $manifest['band_options'][ReportBand::DETAILS->value]['keep_together']
            ?? false);
    }

    protected function renderBand(ReportBand $band, array $template, array $data, bool $preview = false): string
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
                $html .= $this->renderRows($segment, $data, $preview);
                $html .= $this->renderBrickSafely(\Modules\Core\ReportBuilder\Bricks\PageBreakBrick::class, $entry['config'] ?? [], $data, $preview);
                $segment = [];

                continue;
            }

            $segment[] = $entry;
        }

        $html .= $this->renderRows($segment, $data, $preview);
        $html .= '</div>';

        return $html;
    }

    protected function renderRows(array $entries, array $data, bool $preview = false): string
    {
        $html = '';

        foreach ($this->chunkIntoRows($entries) as $row) {
            $html .= $this->renderRow($row, $data, $preview);
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
    protected function renderRow(array $row, array $data, bool $preview = false): string
    {
        $cells = '';

        foreach ($row as $block) {
            $brickClass = ReportBricksCollection::findById((string) $block['entry']['brick']);

            if ($brickClass === null) {
                continue;
            }

            $config  = is_array($block['entry']['config'] ?? null) ? $block['entry']['config'] : [];
            $inner   = $this->renderBrickSafely($brickClass, $config, $data, $preview);
            $percent = (int) round($block['width']->getGridWidth() / 12 * 100);

            $cells .= '<td class="report-block" style="width: ' . $percent . '%; vertical-align: top; padding: 0;">'
                . $inner
                . '</td>';
        }

        return '<table class="report-row" style="width: 100%; border-collapse: collapse;"><tr>' . $cells . '</tr></table>';
    }

    /**
     * @param class-string<\Modules\Core\ReportBuilder\ReportBrick>|string $brickClass
     */
    protected function renderBrickSafely(string $brickClass, array $config, array $data, bool $preview = false): string
    {
        $id = method_exists($brickClass, 'getId') ? $brickClass::getId() : $brickClass;

        try {
            $filteredConfig = method_exists($brickClass, 'filterConfig') ? $brickClass::filterConfig($config) : $config;

            if ($preview && method_exists($brickClass, 'toPreviewHtml')) {
                return (string) $brickClass::toPreviewHtml($filteredConfig);
            }

            return (string) $brickClass::toHtml($filteredConfig, $data);
        } catch (Throwable $e) {
            Log::warning("Report brick {$id} failed: {$e->getMessage()}", ['exception' => $e]);

            return "<!-- report brick {$id} failed to render -->";
        }
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
