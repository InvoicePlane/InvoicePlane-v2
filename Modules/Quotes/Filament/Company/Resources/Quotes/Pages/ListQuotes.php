<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Services\QuoteService;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    $data['quoteItems'] = [
                        [
                            'product_id'   => null,  // Optional: Preselect a default or leave empty
                            'product_name' => '',
                            'item_name'    => '',
                            'quantity'     => 1,
                            'price'        => 0,
                            'discount'     => 0,
                            'subtotal'     => 0,
                        ],
                    ];

                    return $data;
                })
                ->action(function (array $data) {
                    app(QuoteService::class)->createQuote($data);
                })
                ->modalWidth('full'),
        ];
    }
}
