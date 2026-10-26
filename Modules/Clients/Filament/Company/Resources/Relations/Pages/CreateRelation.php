<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Clients\Services\RelationService;

class CreateRelation extends CreateRecord
{
    protected static string $resource = RelationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(RelationService::class)->createRelation($data);
    }
}
