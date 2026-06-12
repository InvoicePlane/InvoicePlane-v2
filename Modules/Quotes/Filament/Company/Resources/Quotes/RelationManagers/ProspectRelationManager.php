<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;

class ProspectRelationManager extends RelationManager
{
    protected static string $relationship = 'prospect';

    protected static ?string $relatedResource = RelationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
