<?php

namespace Modules\Core\Tests\Feature;

use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Core\Providers\CompanyPanelProvider;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;

/**
 * Covers extending the sidebar quick-create ("+") affordance (originally
 * added for Expenses only, GH #617) to Relations, Products, Invoices,
 * Quotes and Payments, via CompanyPanelProvider::withQuickCreate().
 *
 * Resources without a dedicated `create` page (modal-only, creating records
 * through a CreateAction on their list page) get a quick-create URL pointing
 * at the index page with `?action=create`, which Filament's own
 * `#[Url(as: 'action')]`-bound `$defaultAction` property auto-mounts on load
 * (see vendor/filament/filament resources/views components/page/index.blade.php
 * `wire:init="mountAction(...)"`). Resources with a dedicated create page
 * (Invoices, Quotes, Expenses) link straight to it instead.
 */
class CompanyPanelQuickCreateWiringTest extends AbstractCompanyPanelTestCase
{
    public static function modalOnlyResources(): array
    {
        return [
            'Relations' => [RelationResource::class],
            'Products'  => [ProductResource::class],
            'Payments'  => [PaymentResource::class],
        ];
    }

    public static function dedicatedCreatePageResources(): array
    {
        return [
            'Invoices' => [InvoiceResource::class],
            'Quotes'   => [QuoteResource::class],
            'Expenses' => [ExpenseResource::class],
        ];
    }

    #[Test]
    #[DataProvider('modalOnlyResources')]
    public function it_points_modal_only_resources_at_the_index_page_with_an_auto_mount_query_string(string $resourceClass): void
    {
        /* Arrange */
        $this->actingAs($this->user);

        /* Act */
        $items = $this->withQuickCreate($resourceClass);

        /* Assert */
        $this->assertNotEmpty($items);
        $url = $items[0]->getExtraAttributeBag()->get('data-quick-create-url');
        $this->assertSame($resourceClass::getUrl('index', ['action' => 'create']), $url);
        $this->assertStringContainsString('?action=create', $url);
    }

    #[Test]
    #[DataProvider('dedicatedCreatePageResources')]
    public function it_points_dedicated_create_page_resources_at_their_create_page_without_a_query_string(string $resourceClass): void
    {
        /* Arrange */
        $this->actingAs($this->user);

        /* Act */
        $items = $this->withQuickCreate($resourceClass);

        /* Assert */
        $url = $items[0]->getExtraAttributeBag()->get('data-quick-create-url');
        $this->assertSame($resourceClass::getUrl('create'), $url);
        $this->assertStringNotContainsString('action=create', $url);
    }

    #[Test]
    public function it_omits_the_quick_create_url_when_the_user_cannot_create(): void
    {
        /* Arrange: strip the CUSTOMER_ADMIN role so no create-invoices permission remains */
        $this->actingAs($this->user);
        $this->user->syncRoles([]);
        $this->user->forgetCachedPermissions();

        /* Act */
        $items = $this->withQuickCreate(InvoiceResource::class);

        /* Assert */
        $this->assertNull($items[0]->getExtraAttributeBag()->get('data-quick-create-url'));
    }

    private function withQuickCreate(string $resourceClass): array
    {
        $method = new ReflectionMethod(CompanyPanelProvider::class, 'withQuickCreate');
        $method->setAccessible(true);

        return $method->invoke(null, $resourceClass);
    }
}
