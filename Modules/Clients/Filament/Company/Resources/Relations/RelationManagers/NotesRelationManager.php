<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\UserRole;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('content')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content')->wrap(),
                TextColumn::make('noted_at')->dateTime(),
            ])
            ->recordActions([
                EditAction::make()->authorize(fn (): bool => $this->canManageNotes()),
                DeleteAction::make()->authorize(fn (): bool => $this->canManageNotes()),
            ]);
    }

    protected function canManageNotes(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::CUSTOMER_ADMIN->value,
            ...UserRole::elevated(),
        ]) ?? false;
    }
}
