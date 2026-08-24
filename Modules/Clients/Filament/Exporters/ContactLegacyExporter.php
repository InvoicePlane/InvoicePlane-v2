<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Clients\Models\Contact;
use Modules\Core\Filament\Exporters\BaseExporter;

class ContactLegacyExporter extends BaseExporter
{
    protected static ?string $model = Contact::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('relation_id')
                ->label(trans('ip.relation_id'))
                ->formatStateUsing(fn ($state, Contact $record) => $record->relation?->trading_name ?? $record->relation?->company_name ?? ''),
            ExportColumn::make('type')
                ->label(trans('ip.type'))
                ->formatStateUsing(fn ($state, Contact $record) => $record->relation?->relation_type?->label() ?? ''),
            ExportColumn::make('full_name')
                ->label(trans('ip.contact_name'))
                ->formatStateUsing(fn ($state, Contact $record) => $record->full_name),
            ExportColumn::make('email')
                ->label(trans('ip.email')),
            ExportColumn::make('phone')
                ->label(trans('ip.phone')),
            ExportColumn::make('gender')
                ->label(trans('ip.gender'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.contact');
    }
}
