<?php

namespace Modules\Core\Filament\Exporters;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

abstract class BaseExporter extends Exporter
{
    abstract protected static function getEntityName(): string;

    public static function getCompletedNotificationBody(Export $export): string
    {
        $entityName = static::getEntityName();

        $body = trans('ip.export_completed', [
            'entity' => $entityName,
            'count'  => number_format($export->successful_rows),
            'rows'   => trans_choice('ip.row', $export->successful_rows),
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . trans('ip.export_failed_rows', [
                'count' => number_format($failedRowsCount),
                'rows'  => trans_choice('ip.row', $failedRowsCount),
            ]);
        }

        return $body;
    }
}
