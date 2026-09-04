<?php

namespace Modules\Core\ReportBuilder;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\ReportBuilder\Bricks\DetailCustomerAgingBrick;
use Modules\Core\ReportBuilder\Bricks\DetailExpenseBrick;
use Modules\Core\ReportBuilder\Bricks\DetailInvoiceProductBrick;
use Modules\Core\ReportBuilder\Bricks\DetailItemsBrick;
use Modules\Core\ReportBuilder\Bricks\DetailQuoteProductBrick;
use Modules\Core\ReportBuilder\Bricks\FooterNotesBrick;
use Modules\Core\ReportBuilder\Bricks\FooterSummaryBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTermsBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTotalsBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderClientBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderCompanyBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderInvoiceMetaBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderQuoteMetaBrick;
use Modules\Core\ReportBuilder\Bricks\PageBreakBrick;
use Modules\Core\ReportBuilder\Bricks\SpacerBrick;

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
     * Get the bricks allowed in the given band, optionally narrowed to those
     * that also apply to the given document type (e.g. an invoice template's
     * builder shouldn't offer a quote-only metadata brick).
     *
     * @return array<class-string>
     */
    public static function forBand(ReportBand $band, ?ReportTemplateType $type = null): array
    {
        return array_values(array_filter(
            self::all(),
            fn (string $brick): bool => in_array($band, $brick::allowedBands(), true)
                && ($type === null || in_array($type, $brick::allowedTypes(), true)),
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
        ];
    }

    /**
     * Get detail section bricks.
     *
     * HeaderProjectBrick, DetailTasksBrick, DetailInvoiceProjectBrick and
     * DetailQuoteProjectBrick are intentionally not registered here: invoices
     * and quotes have no foreign key to a Project/Task in this schema (a
     * Project belongs to a customer, not to a specific invoice/quote), so
     * ReportDataMapper has no well-defined data to feed them. Wiring them up
     * would mean guessing product intent (which project? all of the
     * customer's?) rather than fixing a bug. Re-register once that data path
     * is defined; the brick classes and views are left in place for that.
     *
     * @return array<class-string>
     */
    public static function detail(): array
    {
        return [
            DetailItemsBrick::class,
            DetailInvoiceProductBrick::class,
            DetailQuoteProductBrick::class,
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
