<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Blade;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Companion regression check for GH #617: the shared vendor sidebar item
 * view (resources/views/vendor/filament-panels/components/sidebar/item.blade.php)
 * was overridden to add an optional quick-create affordance. That view is
 * used by every panel (admin/company/user), so this proves the override is
 * a byte-for-byte no-op for an ordinary admin panel item that never opts
 * into the `data-quick-create-url` extra attribute.
 */
class SidebarQuickCreateItemAdminPanelRegressionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_renders_ordinary_admin_panel_navigation_items_without_a_quick_create_button(): void
    {
        /* Arrange */
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $item = NavigationItem::make('Users')
            ->icon('heroicon-o-users')
            ->url('https://example.test/admin/users');

        /* Act */
        $html = Blade::render(SidebarQuickCreateItemTest::TEMPLATE, [
            'url'        => $item->getUrl(),
            'label'      => $item->getLabel(),
            'attributes' => \Filament\Support\prepare_inherited_attributes($item->getExtraAttributeBag()),
        ]);

        /* Assert */
        $this->assertStringNotContainsString('fi-sidebar-item-quick-create-btn', $html);
        $this->assertStringContainsString('fi-sidebar-item-btn', $html);
        $this->assertStringContainsString('Users', $html);
    }
}
