<?php

namespace Modules\Core\Providers;

use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Core\Filament\Admin\Resources\CompanyResource;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\TaxRateResource;
use Modules\Core\Filament\Admin\Resources\UserResource;
use Modules\Core\Filament\Company\Pages\Dashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->font(
                'Poppins',
                provider: GoogleFontProvider::class,
            )

            ->colors([
                'primary' => [
                    50  => '#F2F7FD',
                    100 => '#E3EFFB',
                    200 => '#C1DFF6',
                    300 => '#8FC0EE',
                    400 => '#429AE1',
                    500 => '#2684D1',
                    600 => '#1868B1',
                    700 => '#145390',
                    800 => '#154777',
                    900 => '#173C63',
                    950 => '#0F2742',
                ],
                'curious' => [
                    50  => '#F2F7FD',
                    100 => '#E3EFFB',
                    200 => '#C1DFF6',
                    300 => '#8FC0EE',
                    400 => '#429AE1',
                    500 => '#2684D1',
                    600 => '#1868B1',
                    700 => '#145390',
                    800 => '#154777',
                    900 => '#113153',
                    950 => '#0F2742',
                ],
                'darkious' => [
                    50  => '#CCE0FF',
                    100 => '#99B3EB',
                    200 => '#6696D6',
                    300 => '#2D6BB8',
                    400 => '#004DB8',
                    500 => '#003F99',
                    600 => '#002F7A',
                    700 => '#00265F',
                    800 => '#00204F',
                    900 => '#001B3E',
                    950 => '#00102B',
                ],
                'emerald' => [
                    50  => '#ECFDF5',
                    100 => '#D1F8E4',
                    200 => '#A8ECCD',
                    300 => '#6FD9AE',
                    400 => '#3CBF8A',
                    500 => '#30A46B',
                    600 => '#258651',
                    700 => '#1D6840',
                    800 => '#165231',
                    900 => '#0F3E25',
                    950 => '#0A2917',
                ],
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-home')
                            ->url(route('filament.admin.pages.dashboard'))
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard')),
                    ])
                    ->groups([
                        NavigationGroup::make('Companies')
                            ->icon('heroicon-o-building-office')
                            ->items([
                                ...CompanyResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Email Templates')
                            ->icon('heroicon-o-archive-box')
                            ->items([
                                ...EmailTemplateResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Document Groups')
                            ->icon('heroicon-o-archive-box')
                            ->items([
                                ...DocumentGroupResource::getNavigationItems(),
                            ]),
                        /*NavigationGroup::make('Payment Methods')
                            ->icon('heroicon-o-credit-card')
                            ->items([
                                ...PaymentMethodResource::getNavigationItems(),
                            ]),*/
                        NavigationGroup::make('Tax Rates')
                            ->icon('heroicon-o-receipt-percent')
                            ->items([
                                ...TaxRateResource::getNavigationItems(),
                            ]),

                        /*NavigationGroup::make('System Settings')
                            ->icon('heroicon-o-cog-8-tooth')
                            ->items([
                                ...SystemSettingResource::getNavigationItems(),
                            ]),*/

                        /*NavigationGroup::make('Import Data')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->items([
                                ...ImportResource::getNavigationItems(),
                            ]),*/

                        NavigationGroup::make('Users & Roles')
                            ->icon('heroicon-o-users')
                            ->items([
                                ...UserResource::getNavigationItems(),
                                //...RoleResource::getNavigationItems(),
                                //...PermissionResource::getNavigationItems(),
                                //...UserProfileResource::getNavigationItems(),
                            ]),
                    ]);
            })
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(
                in: __DIR__ . '/../Filament/Admin/Resources',
                for: 'Modules\\Core\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: __DIR__ . '/../Filament/Admin/Pages',
                for: 'Modules\\Core\\Filament\\Admin\\Pages'
            )
            ->discoverWidgets(
                in: __DIR__ . '/../Filament/Admin/Widgets',
                for: 'Modules\\Core\\Filament\\Admin\\Widgets'
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()->label('Edit profile'),
                MenuItem::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()->label('Translate Sign Out'),
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
