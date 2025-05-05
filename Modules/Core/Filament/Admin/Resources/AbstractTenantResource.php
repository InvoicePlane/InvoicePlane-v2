<?php

namespace Modules\Core\Filament\Admin\Resources;

use Filament\Resources\Resource;

abstract class AbstractTenantResource extends Resource
{
    public static function updateItemTotals(callable $set, callable $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price    = (float) ($get('price') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $subtotal = max(($quantity * $price) - $discount, 0);

        $set('subtotal', number_format($subtotal, 2, '.', ''));
    }

    public static function updateGrandTotal(callable $set, callable $get, string $itemsField = 'items', string $subtotalField = 'subtotal', string $grandTotalField = 'item_subtotal'): void
    {
        $items = $get($itemsField) ?? [];

        $subtotal = collect($items)
            ->sum(fn ($item) => (float) ($item[$subtotalField] ?? 0));

        $set($grandTotalField, number_format($subtotal, 2, '.', ''));
    }
}
