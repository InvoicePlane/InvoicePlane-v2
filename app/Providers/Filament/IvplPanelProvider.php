<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Clients\Filament\ClientsPlugin;
use Modules\Core\Filament\CorePlugin;
use Modules\Expenses\Filament\ExpensesPlugin;
use Modules\Invoices\Filament\InvoicesPlugin;
use Modules\Products\Filament\ProductsPlugin;
use Modules\Projects\Filament\ProjectsPlugin;
use Modules\Quotes\Filament\QuotesPlugin;

class IvplPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('ivpl')
            ->path('ivpl')
            ->login()
            ->passwordReset()
            ->emailVerification()
            //->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ])
            ->plugins([
                ClientsPlugin::make(),
                CorePlugin::make(),
                ExpensesPlugin::make(),
                InvoicesPlugin::make(),
                ProductsPlugin::make(),
                ProjectsPlugin::make(),
                QuotesPlugin::make(),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()->label('Edit profile'),
                MenuItem::make()
                    ->label('Settings')
                    //->url(fn (): string => Settings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()->label('Translate Sign Out'),
            ]);
    }
}
