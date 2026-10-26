<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class UserMenuLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function it_evaluates_labels_after_the_profile_locale_is_selected(): void
    {
        /* Arrange */
        app()->setLocale('en');
        app('translator')->addLines(['ip.edit_profile' => 'Profil Test', 'ip.settings' => 'Einstellungen Test', 'ip.logout' => 'Abmelden Test', 'ip.my_companies' => 'Firmen Test', 'ip.admin_panel' => 'Verwaltung Test'], 'de');
        auth()->setUser(new User(['name' => 'Test', 'language' => 'de']));
        $panels = Filament::getPanels();
        Filament::setTenant(new Company(['id' => 1, 'search_code' => 'test']), isQuiet: true);

        /* Act */
        app()->setLocale('de');
        $labelsByPanel = [];
        foreach ($panels as $panel) {
            Filament::setCurrentPanel($panel);
            $items                          = collect($panel->getUserMenuItemGroups())->flatten(1);
            $labelsByPanel[$panel->getId()] = $items->map(fn ($item) => $item->getLabel())->all();
        }

        /* Assert */
        foreach ($labelsByPanel as $panelId => $labels) {
            $this->assertContains('Profil Test', $labels, $panelId);
            $this->assertContains('Einstellungen Test', $labels, $panelId);
            $this->assertContains('Abmelden Test', $labels, $panelId);
            if ($panelId === 'company') {
                $this->assertContains('Firmen Test', $labels);
                $this->assertContains('Verwaltung Test', $labels);
            }
        }
    }
}
