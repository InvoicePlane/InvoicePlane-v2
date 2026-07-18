<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Blade;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Covers GH #617 — a quick-create "+" affordance next to the Expenses
 * sidebar nav item, driven by a `data-quick-create-url` extra attribute on
 * the NavigationItem (see Modules\Core\Providers\CompanyPanelProvider) and
 * rendered by the overridden
 * resources/views/vendor/filament-panels/components/sidebar/item.blade.php.
 *
 * These tests exercise the shared vendor view directly (rather than a full
 * Livewire page mount) so they aren't coupled to unrelated
 * canViewAny()/authorization wiring. See
 * SidebarQuickCreateItemAdminPanelRegressionTest for the companion
 * regression check proving the override is a no-op for panels/items that
 * don't opt in.
 */
class SidebarQuickCreateItemTest extends AbstractCompanyPanelTestCase
{
    public const TEMPLATE = <<<'BLADE'
        <ul>
            <x-filament-panels::sidebar.item
                :url="$url"
                :attributes="$attributes"
            >{{ $label }}</x-filament-panels::sidebar.item>
        </ul>
        BLADE;

    // Compiling the vendor sidebar item view hits a stale view-cache file-permission
    // error (touch(): Utime failed) in the ip2-test-php:8.4 image.
    #[Test]
    #[Group('failing')]
    public function it_renders_a_quick_create_button_when_the_navigation_item_declares_a_quick_create_url(): void
    {
        /* Arrange */
        Filament::setCurrentPanel(Filament::getPanel('company'));

        $item = NavigationItem::make('Expenses')
            ->icon('heroicon-o-banknotes')
            ->url('https://example.test/expenses')
            ->extraAttributes([
                'data-quick-create-url' => 'https://example.test/expenses/create',
            ]);

        /* Act */
        $html = Blade::render(self::TEMPLATE, [
            'url'        => $item->getUrl(),
            'label'      => $item->getLabel(),
            'attributes' => \Filament\Support\prepare_inherited_attributes($item->getExtraAttributeBag()),
        ]);

        /* Assert */
        $this->assertStringContainsString('fi-sidebar-item-quick-create-btn', $html);
        $this->assertStringContainsString('href="https://example.test/expenses/create"', $html);

        // Hidden when the sidebar is collapsed to icon-only: gated behind
        // the same expanded-state Alpine directive as the item's own label.
        $this->assertStringContainsString('x-show="$store.sidebar.isOpen"', $html);
    }

    // Compiling the vendor sidebar item view hits a stale view-cache file-permission
    // error (touch(): Utime failed) in the ip2-test-php:8.4 image.
    #[Test]
    #[Group('failing')]
    public function it_does_not_render_a_quick_create_button_when_the_navigation_item_has_no_quick_create_url(): void
    {
        /* Arrange */
        Filament::setCurrentPanel(Filament::getPanel('company'));

        $item = NavigationItem::make('Invoices')
            ->icon('heroicon-o-banknotes')
            ->url('https://example.test/invoices');

        /* Act */
        $html = Blade::render(self::TEMPLATE, [
            'url'        => $item->getUrl(),
            'label'      => $item->getLabel(),
            'attributes' => \Filament\Support\prepare_inherited_attributes($item->getExtraAttributeBag()),
        ]);

        /* Assert */
        $this->assertStringNotContainsString('fi-sidebar-item-quick-create-btn', $html);

        // The item itself still renders normally.
        $this->assertStringContainsString('fi-sidebar-item-btn', $html);
        $this->assertStringContainsString('Invoices', $html);
    }
}
