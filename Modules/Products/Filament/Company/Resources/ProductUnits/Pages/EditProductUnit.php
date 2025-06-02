<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use Modules\Products\Services\ProductUnitService;

class EditProductUnit extends EditRecord
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductUnitService::class)->updateProductUnit($record, $data);
    }
}
