<?php

namespace Modules\Core\Mason;

use Awcodes\Mason\Support\IframeRenderer;

/**
 * Renders the report builder canvas inside Mason's preview iframe.
 *
 * Mason reloads that iframe on every state change by POSTing the blocks to
 * /mason/preview, and the stock renderer paints each block with toHtml() —
 * the print rendering, which expects entity data and so comes out
 * near-empty in the builder. The Alpine component then immediately repaints
 * the same blocks from their base64 `preview` attribute, so the canvas
 * flashed the print rendering on every single edit.
 *
 * Rendering previews here makes both passes agree. The `preview` key is
 * supplied too, because the iframe blade prefers it over `html` — that is
 * the shape the client-side repaint produces.
 *
 * Bound over IframeRenderer in CoreServiceProvider; MasonController resolves
 * the renderer through the container, so the binding is what takes effect.
 */
class ReportIframeRenderer extends IframeRenderer
{
    /**
     * @param array<string, mixed> $block
     */
    public function getBlockHtml(array $block): ?string
    {
        if (($block['type'] ?? null) !== 'masonBrick') {
            return null;
        }

        $id = $block['attrs']['id'] ?? null;

        if (blank($id)) {
            return null;
        }

        $config = is_array($block['attrs']['config'] ?? null) ? $block['attrs']['config'] : [];

        foreach ($this->getBricks() as $brick) {
            if (is_string($brick) && $brick::getId() === $id) {
                return is_subclass_of($brick, ReportBrick::class)
                    ? $brick::toPreviewHtml($config)
                    : $brick::toHtml($config);
            }
        }

        return view('mason::components.unregistered-brick', ['label' => $id])->render();
    }

    public function toHtml(?string $layout = null): string
    {
        $blocks = array_map(function (array $block, int $index): array {
            $html = $this->getBlockHtml($block);
            $id   = $block['attrs']['id'] ?? null;

            return [
                'index'   => $index,
                'id'      => $id,
                'config'  => $block['attrs']['config'] ?? [],
                'html'    => $html,
                'preview' => base64_encode((string) $html),
                'label'   => $this->getBlockLabel($id),
            ];
        }, $this->blocks, array_keys($this->blocks));

        $layoutToUse = $layout ?? config('mason.iframe.layout');

        if ($layoutToUse) {
            return view($layoutToUse, ['blocks' => $blocks])->render();
        }

        return view('mason::iframe-preview', ['blocks' => $blocks])->render();
    }
}
