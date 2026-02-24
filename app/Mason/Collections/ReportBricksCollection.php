<?php

namespace App\Mason\Collections;

use App\Mason\Bricks\DetailItemsBrick;
use App\Mason\Bricks\FooterNotesBrick;
use App\Mason\Bricks\FooterTotalsBrick;
use App\Mason\Bricks\HeaderClientBrick;
use App\Mason\Bricks\HeaderCompanyBrick;
use App\Mason\Bricks\HeaderInvoiceMetaBrick;

/**
 * Collection of Mason Bricks for Report Templates.
 *
 * Organizes available bricks by their functional area (header, detail, footer).
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
        ];
    }
}
