<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Exporters\BaseExporter;

class RelationLegacyExporter extends BaseExporter
{
    protected static ?string $model = Relation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('relation_type')
                ->label(trans('ip.relation_type'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('trading_name')
                ->label(trans('ip.trading_name'))
                ->formatStateUsing(fn ($state, Relation $record) => $record->trading_name ?? $record->company_name),
            ExportColumn::make('email')
                ->label(trans('ip.email')),
            ExportColumn::make('phone')
                ->label(trans('ip.phone')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.relation');
    }
}
