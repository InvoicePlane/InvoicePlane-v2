<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Services\ReportBlockService;

class ReportBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('block_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('width')
                    ->sortable(),
                TextColumn::make('data_source')
                    ->sortable(),
                TextColumn::make('default_band')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_system')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make('delete')
                        ->action(function (ReportBlock $record) {
                            app(ReportBlockService::class)->deleteReportBlock($record);
                        }),
                ]),
            ]);
    }
}
