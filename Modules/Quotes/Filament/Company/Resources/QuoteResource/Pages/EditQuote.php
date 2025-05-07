<?php

namespace Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages;

use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\EditQuote;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Models\Company;

use Modules\Quotes\Filament\Company\Resources\QuoteResource;

use Filament\Resources\Pages\EditRecord;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
