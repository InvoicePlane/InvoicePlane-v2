<?php

namespace Modules\Core\Mason;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Mason\Bricks\DetailCustomerAgingBrick;
use Modules\Core\Mason\Bricks\DetailExpenseBrick;
use Modules\Core\Mason\Bricks\DetailInvoiceProductBrick;
use Modules\Core\Mason\Bricks\DetailInvoiceProjectBrick;
use Modules\Core\Mason\Bricks\DetailItemsBrick;
use Modules\Core\Mason\Bricks\DetailQuoteProductBrick;
use Modules\Core\Mason\Bricks\DetailQuoteProjectBrick;
use Modules\Core\Mason\Bricks\DetailTasksBrick;
use Modules\Core\Mason\Bricks\FooterNotesBrick;
use Modules\Core\Mason\Bricks\FooterSummaryBrick;
use Modules\Core\Mason\Bricks\FooterTermsBrick;
use Modules\Core\Mason\Bricks\FooterTotalsBrick;
use Modules\Core\Mason\Bricks\HeaderClientBrick;
use Modules\Core\Mason\Bricks\HeaderCompanyBrick;
use Modules\Core\Mason\Bricks\HeaderInvoiceMetaBrick;
use Modules\Core\Mason\Bricks\HeaderProjectBrick;
use Modules\Core\Mason\Bricks\HeaderQuoteMetaBrick;
use Modules\Core\Mason\Bricks\PageBreakBrick;
use Modules\Core\Mason\Bricks\SpacerBrick;

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
            ...self::utility(),
        ];
    }

    /**
     * Get utility bricks allowed in every band.
     *
     * @return array<class-string>
     */
    public static function utility(): array
    {
        return [
            PageBreakBrick::class,
            SpacerBrick::class,
        ];
    }

    /**
     * Get the bricks allowed in the given band.
     *
     * @return array<class-string>
     */
    public static function forBand(ReportBand $band): array
    {
        return array_values(array_filter(
            self::all(),
            fn (string $brick): bool => in_array($band, $brick::allowedBands(), true),
        ));
    }

    /**
     * Find a brick class by its brick id.
     *
     * @return class-string|null
     */
    public static function findById(string $id): ?string
    {
        foreach (self::all() as $brick) {
            if ($brick::getId() === $id) {
                return $brick;
            }
        }

        return null;
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
            DetailInvoiceProductBrick::class,
            DetailInvoiceProjectBrick::class,
            DetailQuoteProductBrick::class,
            DetailQuoteProjectBrick::class,
            DetailCustomerAgingBrick::class,
            DetailExpenseBrick::class,
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
