<?php

namespace App\Mason\Collections;

use App\Mason\Bricks\DetailItemsBrick;
use App\Mason\Bricks\DetailTasksBrick;
use App\Mason\Bricks\FooterNotesBrick;
use App\Mason\Bricks\FooterSummaryBrick;
use App\Mason\Bricks\FooterTermsBrick;
use App\Mason\Bricks\FooterTotalsBrick;
use App\Mason\Bricks\HeaderClientBrick;
use App\Mason\Bricks\HeaderCompanyBrick;
use App\Mason\Bricks\HeaderInvoiceMetaBrick;
use App\Mason\Bricks\HeaderProjectBrick;
use App\Mason\Bricks\HeaderQuoteMetaBrick;

/**
 * Collection of Mason Bricks for Report Templates.
 *
 * Organizes available bricks by their functional area (header, detail, footer).
 * Supports multiple entity types: Invoices, Quotes, Projects, Clients, Tasks.
 */
class ReportBricksCollection
{
    /**
     * Get all available bricks.
     *
     * @return array<class-string>
     */
    public static function all(): array
    {
        return [
            ...self::header(),
            ...self::detail(),
            ...self::footer(),
        ];
    }

    /**
     * Get header section bricks.
     *
     * @return array<class-string>
     */
    public static function header(): array
    {
        return [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            HeaderQuoteMetaBrick::class,
            HeaderProjectBrick::class,
        ];
    }

    /**
     * Get detail section bricks.
     *
     * @return array<class-string>
     */
    public static function detail(): array
    {
        return [
            DetailItemsBrick::class,
            DetailTasksBrick::class,
        ];
    }

    /**
     * Get footer section bricks.
     *
     * @return array<class-string>
     */
    public static function footer(): array
    {
        return [
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
            FooterTermsBrick::class,
            FooterSummaryBrick::class,
        ];
    }
}
