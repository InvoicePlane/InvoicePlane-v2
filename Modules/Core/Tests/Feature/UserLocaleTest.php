<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Modules\Core\Http\Middleware\SetUserLocale;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class UserLocaleTest extends AbstractTestCase
{
    public static function languages(): array
    {
        return [['de', 'de'], ['fr', 'fr'], ['en', 'en'], [null, 'en'], ['', 'en'], ['../../invalid', 'en']];
    }

    #[Test]
    #[DataProvider('languages')]
    public function it_uses_supported_user_languages_for_the_whole_request(?string $language, string $expected): void
    {
        app()->setLocale('en');
        $request = Request::create('/');
        $request->setUserResolver(fn () => new User(['language' => $language]));

        $response = app(SetUserLocale::class)->handle($request, function () use ($expected) {
            $this->assertSame($expected, app()->getLocale());

            return response('ok');
        });

        $this->assertSame('ok', $response->getContent());
        $this->assertSame($expected, app()->getLocale());
    }

    #[Test]
    public function it_keeps_the_application_locale_for_guests(): void
    {
        app()->setLocale('de');
        $request = Request::create('/login');
        $request->setUserResolver(fn () => null);
        app(SetUserLocale::class)->handle($request, function () {
            $this->assertSame('de', app()->getLocale());

            return response('ok');
        });
        $this->assertSame('de', app()->getLocale());
    }

    #[Test]
    public function it_keeps_the_locale_after_livewire_replays_the_middleware_pipeline(): void
    {
        app()->setLocale('en');
        $request = Request::create('/livewire/update', 'POST');
        $request->setUserResolver(fn () => new User(['language' => 'de']));
        app(SetUserLocale::class)->handle($request, fn () => response('middleware checked'));
        // Livewire runs persistent middleware before hydrating/rendering the component.
        $this->assertSame('de', app()->getLocale());
    }

    #[Test]
    public function it_registers_the_middleware_on_every_panel_and_livewire_updates(): void
    {
        foreach (Filament::getPanels() as $panel) {
            $this->assertContains(SetUserLocale::class, $panel->getMiddleware(), $panel->getId());
        }
        $this->assertContains(SetUserLocale::class, app(\Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware::class)->getPersistentMiddleware());
    }
}
