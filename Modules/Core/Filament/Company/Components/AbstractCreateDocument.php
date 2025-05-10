<?php

namespace Modules\Core\Filament\Company\Components;

class AbstractCreateDocument
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] ??= 'draft';

        return $data;
    }
}
