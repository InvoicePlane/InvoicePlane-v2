<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Services\ContactService;
use Modules\Core\Helpers\EnumHelper;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('relation.company_name')->limit(10)->label(trans('ip.company_name'))->searchable()->sortable()->toggleable(),
                TextColumn::make('relation.relation_type')
                    ->limit(10)
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RelationType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        $status = EnumHelper::safeEnum(RelationType::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label(trans('ip.contact_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('primary_email')
                    ->label(trans('ip.email'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('primary_phone')
                    ->label(trans('ip.phone'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('gender')
                    ->hiddenFrom('sm')
                    ->label(trans('ip.gender'))
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(Gender::class, $state);

                        return $status?->label() ?? '-';
                    }),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Contact $record, array $data) {
                            app(ContactService::class)->updateContact($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Contact $record, array $data) {
                            app(ContactService::class)->deleteContact($record, $data);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
