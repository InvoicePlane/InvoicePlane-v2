<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Clients\Models\Relation;

class RelationExporter extends Exporter
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

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your relation export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
