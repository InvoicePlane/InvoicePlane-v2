<?php

namespace Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\CreateQuote;

use Modules\Core\Models\Company;

use Modules\Quotes\Filament\Company\Resources\QuoteResource;

use Filament\Resources\Pages\CreateRecord;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
