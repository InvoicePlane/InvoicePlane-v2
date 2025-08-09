<?php

namespace Modules\Core\Providers;

use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Core\Filament\Company\Pages\Dashboard;
use Modules\Core\Filament\Pages\Auth\EditProfile;
use Modules\Core\Http\Middleware\ConfigureTenant;
use Modules\Core\Http\Middleware\EnsureUserCanAccessCompany;
use Modules\Core\Http\Middleware\SetTenantFromQueryString;
use Modules\Core\Models\Company;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Filament\Company\Widgets\RecentInvoicesWidget;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Products\Filament\Company\Resources\ProductCategories\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Filament\Company\Widgets\RecentQuotesWidget;

class CompanyPanelProvider extends PanelProvider
{
    public function panel(Panel $companyPanel): Panel
    {
        /** @var Panel $companyPanel */
        $panel = $companyPanel
            // #region Panel Configuration

            ->default()
            ->id('company')
            ->path('')
            ->login()
            ->profile(EditProfile::class, isSimple: false)
            ->passwordReset()
            ->emailVerification()
            ->maxContentWidth(Width::Full)
            ->font('Poppins', provider: GoogleFontProvider::class)
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->tenantMenu(false)
            // #endregion

            // #region Tenant Configuration
            ->tenant(
                Company::class,
                slugAttribute: 'search_code',
            )
            ->homeUrl(function ($panel, $company) {
                $tenant = request('tenant');
                //\Filament\Facades\Filament::getTenant()?->search_code

                return route('filament.company.pages.dashboard', ['tenant' => $tenant]);
            })

            ->tenantMiddleware([
                SetTenantFromQueryString::class,
                ConfigureTenant::class,
                EnsureUserCanAccessCompany::class,
            ], isPersistent: true)
            // #endregion

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
                PaymentResource::class,
                ProductResource::class,
                ProductUnitResource::class,
                ProductCategoryResource::class,
                ProjectResource::class,
                TaskResource::class,
                QuoteResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Company/Pages'), for: 'App\Filament\Company\Pages')
            ->discoverWidgets(in: app_path('Filament/Company/Widgets'), for: 'App\Filament\Company\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                RecentQuotesWidget::class,
                RecentInvoicesWidget::class,
                //RecentProjectsWidget::class,
                //RecentTasksWidget::class,
                //RecentExpensesWidget::class,
                //RecentPaymentsWidget::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $tenant = request('tenant');
                //\Filament\Facades\Filament::getTenant()?->search_code

                return $builder
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-home')
                            ->url(route('filament.company.pages.dashboard', ['tenant' => $tenant]))
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.company.pages.dashboard')),
                    ])
                    ->groups([
                        NavigationGroup::make('Customers')
                            //->icon('heroicon-o-user-group')
                            ->items([
                                ...RelationResource::getNavigationItems(),
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
                            ]),

                        NavigationGroup::make('Expenses')
                            //->icon('heroicon-o-banknotes')
                            ->items([
                                ...ExpenseResource::getNavigationItems(),
                                ...ExpenseCategoryResource::getNavigationItems(),
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
            });
        /*->userMenuItems([
                'profile' => fn (Action $action) => $action
                    ->label(trans('ip.edit_profile'))
                    ->icon('heroicon-o-user')
                    ->url(EditProfile::getUrl()),
                Action::make('settings')
                    ->label(trans('ip.settings'))
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => fn (Action $action) => $action
                    ->label(trans(trans('ip.logout')))
                    ->icon(Heroicon::OutlinedArrowRightStartOnRectangle),
            ])*/

        return $panel;
    }
}
