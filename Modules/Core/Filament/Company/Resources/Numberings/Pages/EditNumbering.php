<?php

namespace Modules\Core\Filament\Company\Resources\Numberings\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Company\Resources\Numberings\NumberingResource;

class EditNumbering extends EditRecord
{
    protected static string $resource = NumberingResource::class;

    protected static ?string $title = 'Edit Numbering Scheme';

    /**
     * Prevent changing company_id via form manipulation.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure company_id remains unchanged
        unset($data['company_id']);

        return $data;
    }
}
