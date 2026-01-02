<?php

namespace Modules\Core\Enums;

enum ReportBand: string
{
    case DETAILS = 'details';
    case FOOTER = 'footer';
    case GROUP_FOOTER = 'group_footer';
    case GROUP_HEADER = 'group_header';
    case HEADER = 'header';

    /**
     * Get the display label for the band.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::HEADER => 'Header',
            self::GROUP_HEADER => 'Group Header',
            self::DETAILS => 'Details',
            self::GROUP_FOOTER => 'Group Footer',
            self::FOOTER => 'Footer',
        };
    }

    /**
     * Get the CSS color class for the band.
     * Uses Filament's semantic color names.
     */
    public function getColorClass(): string
    {
        return match ($this) {
            self::HEADER => 'bg-success-500 dark:bg-success-600',
            self::GROUP_HEADER => 'bg-info-500 dark:bg-info-600',
            self::DETAILS => 'bg-primary-500 dark:bg-primary-600',
            self::GROUP_FOOTER => 'bg-info-500 dark:bg-info-600',
            self::FOOTER => 'bg-success-500 dark:bg-success-600',
        };
    }

    /**
     * Get the CSS border color class for the band.
     */
    public function getBorderColorClass(): string
    {
        return match ($this) {
            self::HEADER => 'border-warning-700 dark:border-warning-800',
            self::GROUP_HEADER => 'border-danger-700 dark:border-danger-800',
            self::DETAILS => 'border-primary-700 dark:border-primary-800',
            self::GROUP_FOOTER => 'border-success-700 dark:border-success-800',
            self::FOOTER => 'border-info-700 dark:border-info-800',
        };
    }

    /**
     * Get the order/position for sorting bands.
     */
    public function getOrder(): int
    {
        return match ($this) {
            self::HEADER => 1,
            self::GROUP_HEADER => 2,
            self::DETAILS => 3,
            self::GROUP_FOOTER => 4,
            self::FOOTER => 5,
        };
    }
}
