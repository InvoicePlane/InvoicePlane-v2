<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Pages;

use Filament\Actions\Action;
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
                ->action(function (array $data, Action $action) {
                    $quote = app(QuoteService::class)->createQuote($data);

                    if (filled($quote->quote_number)) {
                        $action->successNotificationTitle(
                            trans('ip.quote_created_with_number', ['number' => $quote->quote_number])
                        );
                    }
                })
                ->modalWidth('full'),
        ];
    }
}
