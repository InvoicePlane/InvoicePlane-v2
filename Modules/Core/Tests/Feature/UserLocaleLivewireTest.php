<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Drawer\Utils;
use Livewire\Livewire;
use Modules\Core\Http\Middleware\SetUserLocale;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Core\Tests\Fixtures\LocaleProbe;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class UserLocaleLivewireTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_uses_the_profile_locale_during_a_real_panel_livewire_update(): void
    {
        /* Arrange */
        $user = $this->superAdmin();
        $user->update(['language' => 'de']);
        $this->actingAs($user);
        $panel = Filament::getPanel('admin');
        Livewire::component('user-locale-probe', LocaleProbe::class);
        Route::get('/admin/user-locale-probe', fn () => Blade::render('<livewire:user-locale-probe />'))
            ->middleware([...$panel->getMiddleware(), ...$panel->getAuthMiddleware()]);
        $middleware = $this->partialMock(SetUserLocale::class);

        /* Act */
        $initialResponse = $this->get('/admin/user-locale-probe');
        $snapshot        = Utils::extractAttributeDataFromHtml($initialResponse->getContent(), 'wire:snapshot');

        // Simulate a new request starting with the application default locale.
        Livewire::flushState();
        app()->setLocale('en');

        $response = $this->postJson(Livewire::getUpdateUri(), [
            'components' => [[
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'updates'  => [],
                'calls'    => [['method' => 'captureLocale', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true']);

        /* Assert */
        $initialResponse->assertOk();
        $response->assertOk();
        $updatedSnapshot = json_decode($response->json('components.0.snapshot'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('admin/user-locale-probe', $updatedSnapshot['memo']['path']);
        $this->assertSame('de', $updatedSnapshot['data']['localeDuringAction']);
        $this->assertStringContainsString('Action: de; Render: de', $response->json('components.0.effects.html'));
        // Livewire also restores snapshot locales; prove the real middleware ran on both requests.
        $middleware->shouldHaveReceived('handle')->twice();
    }
}
