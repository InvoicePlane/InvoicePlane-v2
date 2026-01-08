<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;

class CustomerRelationManager extends RelationManager
{
    protected static string $relationship = 'customer';

    protected static ?string $relatedResource = RelationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
