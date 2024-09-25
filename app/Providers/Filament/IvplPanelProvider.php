<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
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
use Modules\Invoices\Filament\InvoicesPlugin;
use Modules\Payments\Filament\PaymentsPlugin;
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
                'primary' => [
                    50  => '242, 247, 253',
                    100 => '227, 239, 251',
                    200 => '193, 223, 246',
                    300 => '143, 192, 238',
                    400 => '66, 154, 225',
                    500 => '38, 132, 209',
                    600 => '24, 104, 177',
                    700 => '20, 83, 144',
                    800 => '21, 71, 119',
                    900 => '23, 60, 99',
                    950 => '15, 39, 66)',
                ],
                'curious' => [
                    50  => '242, 247, 253',
                    100 => '227, 239, 251',
                    200 => '193, 223, 246',
                    300 => '143, 192, 238',
                    400 => '66, 154, 225',
                    500 => '38, 132, 209',
                    600 => '24, 104, 177',
                    700 => '20, 83, 144',
                    800 => '21, 71, 119',
                    900 => '17, 49, 83',
                    950 => '15, 39, 66)',
                ],
                'darkious' => [
                    50  => '204, 224, 255',
                    100 => '153, 179, 235',
                    200 => '102, 150, 214',
                    300 => '45, 107, 184',
                    400 => '0, 77, 184',
                    500 => '0, 63, 153',
                    600 => '0, 47, 122',
                    700 => '0, 38, 95',
                    800 => '0, 32, 79',
                    900 => '0, 27, 62',
                    950 => '0, 16, 43)',
                ],
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
                InvoicesPlugin::make(),
                PaymentsPlugin::make(),
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
