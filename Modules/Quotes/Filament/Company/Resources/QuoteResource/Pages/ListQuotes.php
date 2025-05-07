<?php

namespace Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages;

use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\ListQuotes;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Models\Company;

use Modules\Quotes\Filament\Company\Resources\QuoteResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
