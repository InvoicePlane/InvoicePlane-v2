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
        /* Arrange */
        app()->setLocale('en');
        $request = Request::create('/');
        $request->setUserResolver(fn () => new User(['language' => $language]));
        $downstreamLocale = null;

        /* Act */
        $response = app(SetUserLocale::class)->handle($request, function () use (&$downstreamLocale) {
            $downstreamLocale = app()->getLocale();

            return response('ok');
        });

        /* Assert */
        $this->assertSame($expected, $downstreamLocale);
        $this->assertSame('ok', $response->getContent());
        $this->assertSame($expected, app()->getLocale());
    }

    #[Test]
    public function it_keeps_the_application_locale_for_guests(): void
    {
        /* Arrange */
        app()->setLocale('de');
        $request = Request::create('/login');
        $request->setUserResolver(fn () => null);
        $downstreamLocale = null;

        /* Act */
        app(SetUserLocale::class)->handle($request, function () use (&$downstreamLocale) {
            $downstreamLocale = app()->getLocale();

            return response('ok');
        });

        /* Assert */
        $this->assertSame('de', $downstreamLocale);
        $this->assertSame('de', app()->getLocale());
    }

    #[Test]
    public function it_keeps_the_locale_after_livewire_replays_the_middleware_pipeline(): void
    {
        /* Arrange */
        app()->setLocale('en');
        $request = Request::create('/livewire/update', 'POST');
        $request->setUserResolver(fn () => new User(['language' => 'de']));

        /* Act */
        app(SetUserLocale::class)->handle($request, fn () => response('middleware checked'));

        /* Assert */
        // Livewire runs persistent middleware before hydrating/rendering the component.
        $this->assertSame('de', app()->getLocale());
    }

    #[Test]
    public function it_registers_the_middleware_on_every_panel_and_livewire_updates(): void
    {
        /* Arrange */
        $panels               = Filament::getPanels();
        $persistentMiddleware = app(\Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware::class);

        /* Act */
        $panelMiddleware    = collect($panels)->mapWithKeys(fn ($panel) => [$panel->getId() => $panel->getMiddleware()]);
        $livewireMiddleware = $persistentMiddleware->getPersistentMiddleware();

        /* Assert */
        foreach ($panelMiddleware as $panelId => $middleware) {
            $this->assertContains(SetUserLocale::class, $middleware, $panelId);
        }
        $this->assertContains(SetUserLocale::class, $livewireMiddleware);
    }

    #[Test]
    public function it_exposes_the_profile_language_to_account_notifications(): void
    {
        /* Arrange */
        app()->setLocale('en');
        $user = new User(['language' => 'de']);

        /* Act */
        $preferredLocale = $user->preferredLocale();
        $user->language  = 'invalid';
        $fallbackLocale  = $user->preferredLocale();

        /* Assert */
        $this->assertInstanceOf(\Illuminate\Contracts\Translation\HasLocalePreference::class, $user);
        $this->assertSame('de', $preferredLocale);
        $this->assertSame('en', $fallbackLocale);
    }
}
