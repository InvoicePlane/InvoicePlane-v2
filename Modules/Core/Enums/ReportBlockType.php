<?php

namespace Modules\Core\Enums;

enum ReportBlockType: string
{
    case ADDRESS = 'address';
    case DETAILS = 'details';
    case METADATA = 'metadata';
    case TOTALS = 'totals';

    /**
     * Get a human-readable label for the block type.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ADDRESS => trans('ip.report_block_type_address'),
            self::DETAILS => trans('ip.report_block_type_details'),
            self::METADATA => trans('ip.report_block_type_metadata'),
            self::TOTALS => trans('ip.report_block_type_totals'),
        };
    }

    /**
     * Get a description for the block type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ADDRESS => trans('ip.report_block_type_address_desc'),
            self::DETAILS => trans('ip.report_block_type_details_desc'),
            self::METADATA => trans('ip.report_block_type_metadata_desc'),
            self::TOTALS => trans('ip.report_block_type_totals_desc'),
        };
    }
}
