<?php

namespace Modules\Core\Providers;

use Filament\Actions\Action;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Products\Filament\Company\Resources\ProductCategories\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;

class CompanyPanelProvider extends PanelProvider
{
    public function panel(Panel $companyPanel): Panel
    {
        /** @var Panel $companyPanel */
        $panel = $companyPanel
            ->id('company')
            ->path('')
            ->login()
            ->default()
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
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->resources([
                ContactResource::class,
                RelationResource::class,
                ExpenseResource::class,
                ExpenseCategoryResource::class,
                InvoiceResource::class,
                RecurringInvoiceResource::class,
                PaymentResource::class,
                ProductResource::class,
                ProductUnitResource::class,
                ProductCategoryResource::class,
                ProjectResource::class,
                TaskResource::class,
                QuoteResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Company/Pages'), for: 'App\Filament\Company\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Company/Widgets'), for: 'App\Filament\Company\Widgets')
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
                            //->icon('heroicon-o-user-group')
                            ->items([
                                ...RelationResource::getNavigationItems(),
                                ...ContactResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Expenses')
                            //->icon('heroicon-o-banknotes')
                            ->items([
                                ...ExpenseResource::getNavigationItems(),
                                ...ExpenseCategoryResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Quotes')
                            //->icon('heroicon-o-document-text')
                            ->items([
                                ...QuoteResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Invoices')
                            //->icon('heroicon-o-banknotes')
                            ->items([
                                ...InvoiceResource::getNavigationItems(),
                                ...RecurringInvoiceResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Payments')
                            //->icon('heroicon-o-currency-dollar')
                            ->items([
                                ...PaymentResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Resources')
                            //->icon('heroicon-o-archive-box')
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
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                Action::make('profile')
                    ->label(trans('change_password'))
                    ->icon('heroicon-o-user'),
                Action::make('settings')
                    ->label(trans('settings'))
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => fn (Action $action) => $action
                    ->label(trans(trans('ip.logout')))
                    ->icon(Heroicon::OutlinedArrowRightStartOnRectangle),
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

        return $panel;
    }
}
