<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;
use Modules\Products\Services\ProductUnitService;

class EditProductUnit extends EditRecord
{
    protected static string $resource = ProductUnitResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductUnitService::class)->updateProductUnit($record, $data);
    }
}
