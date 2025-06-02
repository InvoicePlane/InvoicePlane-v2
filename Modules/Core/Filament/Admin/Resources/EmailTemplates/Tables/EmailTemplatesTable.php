<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->limit(10)->label(trans('ip.title'))->searchable()->sortable()->toggleable(),
                TextColumn::make('type')->label(trans('ip.type'))->searchable()->sortable()->toggleable(),
                TextColumn::make('subject')->limit(10)->label(trans('ip.subject'))->hiddenFrom('sm')->searchable()->sortable()->toggleable(),
                TextColumn::make('from_name')->limit(10)->label(trans('ip.from_name'))->searchable()->sortable()->toggleable(),
                TextColumn::make('from_email')->limit(10)->label(trans('ip.from_email'))->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title', 'asc');
    }
}
