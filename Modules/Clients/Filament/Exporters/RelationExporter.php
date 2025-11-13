<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Exporters\BaseExporter;

class RelationExporter extends BaseExporter
{
    protected static ?string $model = Relation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('primary_contact')
                ->label(trans('ip.primary_contact')),
            ExportColumn::make('relation_type')
                ->label(trans('ip.relation_type'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('relation_status')
                ->label(trans('ip.relation_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('relation_number')
                ->label(trans('ip.relation_number')),
            ExportColumn::make('company_name')
                ->label(trans('ip.company_name')),
            ExportColumn::make('unique_name')
                ->label(trans('ip.unique_name')),
            ExportColumn::make('coc_number')
                ->label(trans('ip.coc_number')),
            ExportColumn::make('vat_number')
                ->label(trans('ip.vat_number')),
            ExportColumn::make('language')
                ->label(trans('ip.language')),
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
