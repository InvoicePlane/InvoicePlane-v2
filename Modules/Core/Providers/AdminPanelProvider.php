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
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Core\Filament\Admin\Pages\Dashboard;
use Modules\Core\Filament\Admin\Pages\ImportV1Page;
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
        $brand = [
            50  => '#f0f8fd',
            100 => '#deecf9',
            200 => '#bfdcf3',
            300 => '#8ec4ea',
            400 => '#57a4dd',
            500 => '#2589d0',
            600 => '#0078d7',
            700 => '#005a9e',
            800 => '#00477d',
            900 => '#093a63',
            950 => '#062544',
        ];

        $gray = [
            50  => '#f8f9f9',
            100 => '#f3f4f5',
            200 => '#e8eaec',
            300 => '#d7dade',
            400 => '#b6bbc2',
            500 => '#8b919b',
            600 => '#6b717a',
            700 => '#52575f',
            800 => '#383b41',
            900 => '#202226',
            950 => '#131417',
        ];

        return $panel
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile(EditProfile::class, isSimple: false)
            ->passwordReset()
            ->emailVerification()
            ->maxContentWidth(Width::Full)
            ->viteTheme('resources/css/filament/theme.css')
            ->topbar(false)
            ->icons([
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => Heroicon::OutlinedChevronLeft,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => Heroicon::OutlinedChevronRight,
            ])
            ->font(
                'Poppins',
                provider: GoogleFontProvider::class,
            )
            ->colors([
                'primary' => $brand,
                'info'    => $brand,
                'gray'    => $gray,
                'danger'  => Color::hex('#D13438'), // Fluent red
                'warning' => Color::hex('#FFB900'), // Fluent gold
                'success' => Color::hex('#107C10'), // Fluent green
                'emerald' => Color::hex('#8FBCBB'), // Nord Frost teal (nord7) — used by status badges
                'maroon'  => Color::hex('#8F3D42'), // Darkened Nord Aurora red — used by status badges
                'green'   => Color::hex('#A3BE8C'), // Nord Aurora green (nord14) — used by status badges
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
                                ...CompanyResource::getNavigationItems(),
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
                'profile' => MenuItem::make()->label(trans('ip.edit_profile')),
                MenuItem::make()
                    ->label(trans('ip.settings'))
                    ->url('/admin/settings')
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
