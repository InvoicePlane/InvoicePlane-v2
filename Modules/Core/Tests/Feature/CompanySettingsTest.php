<?php

namespace Modules\Core\Tests\Feature;

use Filament\Livewire\Notifications;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Filament\Company\Pages\CompanySettings;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanySettingsTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_persists_the_show_position_numbers_setting(): void
    {
        /* Arrange */
        $this->assertFalse($this->company->getSettingBool('show_line_item_position_numbers'));

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanySettings::class, [
                'tenant' => Str::lower($this->company->search_code),
            ])
            ->fillForm(['show_line_item_position_numbers' => true])
            ->call('save');

        /* Assert */
        $component->assertSuccessful();
        $this->assertTrue($this->company->fresh()->getSettingBool('show_line_item_position_numbers'));
    }

    #[Test]
    public function it_loads_the_setting_value_on_mount(): void
    {
        /* Arrange */
        $this->company->setSetting('show_line_item_position_numbers', true);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanySettings::class, [
                'tenant' => Str::lower($this->company->search_code),
            ]);

        /* Assert */
        $component->assertFormSet([
            'show_line_item_position_numbers' => true,
        ]);
    }

    #[Test]
    public function it_can_disable_the_setting(): void
    {
        /* Arrange */
        $this->company->setSetting('show_line_item_position_numbers', true);
        $this->assertTrue($this->company->fresh()->getSettingBool('show_line_item_position_numbers'));

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanySettings::class, [
                'tenant' => Str::lower($this->company->search_code),
            ])
            ->fillForm(['show_line_item_position_numbers' => false])
            ->call('save');

        /* Assert */
        $component->assertSuccessful();
        $this->assertFalse($this->company->fresh()->getSettingBool('show_line_item_position_numbers'));
    }
}
