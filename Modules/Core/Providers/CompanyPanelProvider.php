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
use Illuminate\Support\Facades\File;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Clients\Filament\Company\Resources\ContactResource;
use Modules\Clients\Filament\Company\Resources\CustomerResource;
use Modules\Core\Filament\Company\Pages\Dashboard;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;
use Modules\Payments\Filament\Company\Resources\PaymentResource;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\ProductResource;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;
use Modules\Projects\Filament\Company\Resources\ProjectResource;
use Modules\Projects\Filament\Company\Resources\TaskResource;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;
use Nwidart\Modules\Facades\Module;

class CompanyPanelProvider extends PanelProvider
{
    public function panel(Panel $companyPanel): Panel
    {
        /** @var Panel $companyPanel */
        $panel = $companyPanel
            ->id('company')
            ->path('')
            ->default()
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->font('Poppins', provider: GoogleFontProvider::class)
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
                            ->url(route('filament.company.pages.dashboard'))
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.company.pages.dashboard')),
                    ])
                    ->groups([
                        NavigationGroup::make('Customers')
                            ->icon('heroicon-o-user-group')
                            ->items([
                                ...CustomerResource::getNavigationItems(),
                                ...ContactResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Expenses')
                            ->icon('heroicon-o-banknotes')
                            ->items([
                                ...ExpenseResource::getNavigationItems(),
                                ...ExpenseCategoryResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Quotes')
                            ->icon('heroicon-o-document-text')
                            ->items([
                                ...QuoteResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Invoices')
                            ->icon('heroicon-o-banknotes')
                            ->items([
                                ...InvoiceResource::getNavigationItems(),
                                ...RecurringInvoiceResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Payments')
                            ->icon('heroicon-o-currency-dollar')
                            ->items([
                                ...PaymentResource::getNavigationItems(),
                                ...PaymentMethodResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Resources')
                            ->icon('heroicon-o-archive-box')
                            ->items([
                                ...ProductResource::getNavigationItems(),
                                ...ProductCategoryResource::getNavigationItems(),
                                ...ProductUnitResource::getNavigationItems(),

                                ...ProjectResource::getNavigationItems(),
                                ...TaskResource::getNavigationItems(),
                            ]),
                    ]);
            })
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'Modules\Core\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'Modules\Core\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'Modules\Core\\Filament\\Widgets'
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()->label(__('change_password')),
                MenuItem::make()->label(__('settings'))->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()->label(__('logout')),
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

        foreach (Module::collections() as $module) {
            $name = $module->getName();
            $base = module_path($name, 'Filament');

            if (File::isDirectory("{$base}/Company/Resources")) {
                $panel = $panel->discoverResources(
                    in: "{$base}/Company/Resources",
                    for: "Modules\\{$name}\\Filament\\Company\\Resources"
                );
            }

            if (File::isDirectory("{$base}/Company/Pages")) {
                $panel = $panel->discoverPages(
                    in: "{$base}/Company/Pages",
                    for: "Modules\\{$name}\\Filament\\Company\\Pages"
                );
            }
        }

        return $panel;
    }
}
