<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\DocumentGroupService;

class DocumentGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->limit(10)
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof DocumentGroupType) {
                            return $state->label();
                        }

                        $status = EnumHelper::safeEnum(DocumentGroupType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        if ($state instanceof DocumentGroupType) {
                            return $state->color();
                        }

                        $status = EnumHelper::safeEnum(DocumentGroupType::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('group_identifier_format')
                    ->searchable()->sortable()->toggleable(),
                TextColumn::make('left_pad')
                    ->numeric()
                    ->searchable()->sortable()->toggleable(),
                TextColumn::make('format')
                    ->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('next_id')
                    ->numeric()
                    ->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(function (array $data, DocumentGroup $record) {
                            $data['name'] = $record->name;

                            return $data;
                        })
                        ->action(function (DocumentGroup $record, array $data) {
                            app(DocumentGroupService::class)->update($record, $data);
                        })
                        ->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
