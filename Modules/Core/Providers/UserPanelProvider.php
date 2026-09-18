<?php

namespace Modules\Core\Providers;

use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->font(
                'Poppins',
                provider: GoogleFontProvider::class,
            )
            ->colors([
                'primary' => Color::hex('#5E81AC'), // Nord Frost (nord10)
                'info'    => Color::hex('#81A1C1'), // Nord Frost (nord9)
                'danger'  => Color::hex('#BF616A'), // Nord Aurora red (nord11)
                'warning' => Color::hex('#EBCB8B'), // Nord Aurora yellow (nord13)
                'success' => Color::hex('#A3BE8C'), // Nord Aurora green (nord14)
                'emerald' => Color::hex('#8FBCBB'), // Nord Frost teal (nord7) — used by status badges
                'maroon'  => Color::hex('#8F3D42'), // Darkened Nord Aurora red — used by status badges
                'green'   => Color::hex('#A3BE8C'), // Nord Aurora green (nord14) — used by status badges
            ])
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'Modules\Core\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'Modules\Core\\Filament\\Pages')
            ->pages([
                //Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'Modules\Core\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()->label(trans('ip.edit_profile')),
                MenuItem::make()
                    ->label(trans('ip.settings'))
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()->label(trans('ip.logout')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
