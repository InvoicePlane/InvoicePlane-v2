<?php

namespace Modules\Core\Providers;

use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Core\Filament\Admin\Pages\Dashboard;
//use Modules\Core\Filament\Admin\Pages\ReportTemplates;
use Modules\Core\Filament\Admin\Pages\RolePermissionsPage;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\Numberings\NumberingResource;
use Modules\Core\Filament\Admin\Resources\TaxRates\TaxRateResource;
use Modules\Core\Filament\Admin\Resources\Users\UserResource;
use Modules\Core\Filament\Pages\Auth\EditProfile;
use Modules\Core\Filament\Pages\Auth\Login;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/company/nord.css')
            ->login(Login::class)
            ->profile(EditProfile::class, isSimple: false)
            ->passwordReset()
            ->emailVerification()
            ->maxContentWidth(Width::Full)
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
            ->pages([
                Dashboard::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->groups([
                        NavigationGroup::make(trans('ip.companies'))
                            //->icon('heroicon-o-building-office')
                            ->items([
                                //...CompanyResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make(trans('ip.email_templates'))
                            //->icon('heroicon-o-archive-box')
                            ->items([
                                ...EmailTemplateResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make(trans('ip.numberings'))
                            //->icon('heroicon-o-archive-box')
                            ->items([
                                ...NumberingResource::getNavigationItems(),
                            ]),
                        /*NavigationGroup::make('Payment Methods')
                            ->icon('heroicon-o-credit-card')
                            ->items([
                                ...PaymentMethodResource::getNavigationItems(),
                            ]),*/
                        NavigationGroup::make(trans('ip.tax_rates'))
                            //->icon('heroicon-o-receipt-percent')
                            ->items([
                                ...TaxRateResource::getNavigationItems(),
                            ]),
                        /*NavigationGroup::make(trans('ip.report_templates'))
                            ->items([
                                ...ReportTemplates::getNavigationItems(),
                            ]),*/

                        /*NavigationGroup::make('System Settings')
                            ->icon('heroicon-o-cog-8-tooth')
                            ->items([
                                ...SystemSettingResource::getNavigationItems(),
                            ]),*/

                        NavigationGroup::make('Import')
                            ->items([
                                ...ImportV1Page::getNavigationItems(),
                            ]),

                        NavigationGroup::make(trans('ip.users_roles'))
                                                    //->icon('heroicon-o-users')
                            ->items([
                                ...UserResource::getNavigationItems(),
                                ...RolePermissionsPage::getNavigationItems(),
                                //...RoleResource::getNavigationItems(),
                                //...PermissionResource::getNavigationItems(),
                                //...UserProfileResource::getNavigationItems(),
                            ]),
                    ]);
            })
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->resources([
                CompanyResource::class,
                NumberingResource::class,
                EmailTemplateResource::class,
                TaxRateResource::class,
                UserResource::class,
            ])
            ->discoverPages(in: base_path('Modules/Core/Filament/Admin/Pages'), for: 'Modules\Core\Filament\Admin\Pages')
            ->discoverWidgets(in: base_path('Modules/Core/Filament/Admin/Widgets'), for: 'Modules\Core\Filament\Admin\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()->label('Edit profile'),
                MenuItem::make()
                    ->label('Settings')
                    ->url('/admin/settings')
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
