<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class UserMenuLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function labels_are_evaluated_after_the_profile_locale_is_selected(): void
    {
        app('translator')->addLines(['ip.edit_profile' => 'Profil Test', 'ip.settings' => 'Einstellungen Test', 'ip.logout' => 'Abmelden Test', 'ip.my_companies' => 'Firmen Test', 'ip.admin_panel' => 'Verwaltung Test'], 'de');
        auth()->setUser(new User(['name' => 'Test', 'language' => 'de']));
        $panels = Filament::getPanels();
        app()->setLocale('de');
        Filament::setTenant(new Company(['id' => 1, 'search_code' => 'test']), isQuiet: true);
        foreach ($panels as $panel) {
            Filament::setCurrentPanel($panel);
            $items  = collect($panel->getUserMenuItemGroups())->flatten(1);
            $labels = $items->map(fn ($item) => $item->getLabel())->all();
            $this->assertContains('Profil Test', $labels, $panel->getId());
            $this->assertContains('Einstellungen Test', $labels, $panel->getId());
            $this->assertContains('Abmelden Test', $labels, $panel->getId());
            if ($panel->getId() === 'company') {
                $this->assertContains('Firmen Test', $labels);
                $this->assertContains('Verwaltung Test', $labels);
            }
        }
    }
}
