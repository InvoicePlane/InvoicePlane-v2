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

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('ip.notes');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('content')
                ->label(fn (): string => trans('fields.content'))
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content')->label(fn (): string => trans('fields.content'))->wrap(),
                TextColumn::make('noted_at')->label(fn (): string => trans('fields.noted_at'))->dateTime(),
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
