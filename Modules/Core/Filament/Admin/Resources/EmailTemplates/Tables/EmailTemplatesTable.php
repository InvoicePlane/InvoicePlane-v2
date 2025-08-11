<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\EmailTemplateService;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->limit(10)
                    ->label(trans('ip.title'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(trans('ip.type'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('subject')
                    ->limit(10)
                    ->label(trans('ip.subject'))
                    ->hiddenFrom('sm')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('from_name')
                    ->limit(10)
                    ->label(trans('ip.from_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('from_email')
                    ->limit(10)
                    ->label(trans('ip.from_email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->action(fn (EmailTemplate $record, array $data) => app(EmailTemplateService::class)->updateEmailTemplate($record, $data))
                        ->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title', 'asc');
    }
}
