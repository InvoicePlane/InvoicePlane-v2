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
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Company\Pages\Auth\EditProfile;
use Modules\Core\Filament\Company\Pages\CompanySettings;
use Modules\Core\Filament\Company\Pages\Dashboard;
use Modules\Core\Filament\Company\Pages\MyCompanies;
use Modules\Core\Filament\Company\Resources\CompanyUsers\CompanyUserResource;
use Modules\Core\Filament\Company\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Company\Resources\NoteTemplates\NoteTemplateResource;
use Modules\Core\Filament\Company\Resources\TaxRates\TaxRateResource;
use Modules\Core\Filament\Pages\Auth\Login;
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

        /** @var Panel $companyPanel */
        $companyPanel = $panel
            // #region Panel Configuration

            ->default()
            ->id('company')
            ->path('')
            ->login(Login::class)
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->maxContentWidth(Width::Full)
            ->viteTheme('resources/css/filament/theme.css')
            ->topbar(false)
            ->icons([
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => Heroicon::OutlinedChevronLeft,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => Heroicon::OutlinedChevronRight,
            ])
            ->font('Poppins', provider: GoogleFontProvider::class)
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
                NoteTemplateResource::class,
                EmailTemplateResource::class,
                CompanyUserResource::class,
                TaxRateResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Company/Pages'), for: 'App\Filament\Company\Pages')
            ->discoverWidgets(in: app_path('Filament/Company/Widgets'), for: 'App\Filament\Company\Widgets')
            ->pages([
                Dashboard::class,
                EditProfile::class,
                MyCompanies::class,
                CompanySettings::class,
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
                                ...self::withQuickCreate(RelationResource::class),
                            ]),

                        NavigationGroup::make('Quotes')
                            //->icon('heroicon-o-document-text')
                            ->items([
                                ...self::withQuickCreate(QuoteResource::class),
                            ]),

                        NavigationGroup::make('Invoices')
                            //->icon('heroicon-o-banknotes')
                            ->items([
                                ...self::withQuickCreate(InvoiceResource::class),
                            ]),

                        NavigationGroup::make('Expenses')
                            //->icon('heroicon-o-banknotes')
                            ->items([
                                ...self::withQuickCreate(ExpenseResource::class),
                                ...(ExpenseCategoryResource::shouldRegisterNavigation() ? ExpenseCategoryResource::getNavigationItems() : []),
                            ]),

                        NavigationGroup::make('Payments')
                            //->icon('heroicon-o-currency-dollar')
                            ->items([
                                ...self::withQuickCreate(PaymentResource::class),
                            ]),

                        NavigationGroup::make('Resources')
                            //->icon('heroicon-o-archive-box')
                            ->items([
                                ...self::withQuickCreate(ProductResource::class),
                                ...(ProductCategoryResource::shouldRegisterNavigation() ? ProductCategoryResource::getNavigationItems() : []),
                                ...(ProductUnitResource::shouldRegisterNavigation() ? ProductUnitResource::getNavigationItems() : []),

                                ...ProjectResource::getNavigationItems(),
                                ...TaskResource::getNavigationItems(),
                            ]),

                        NavigationGroup::make('Settings')
                            //->icon('heroicon-o-cog-6-tooth')
                            ->items([
                                ...NoteTemplateResource::getNavigationItems(),
                                ...EmailTemplateResource::getNavigationItems(),
                                ...CompanyUserResource::getNavigationItems(),
                                ...(TaxRateResource::shouldRegisterNavigation() ? TaxRateResource::getNavigationItems() : []),
                            ]),
                    ]);
            })
            ->userMenuItems([
                Action::make('switch-company')
                    ->label(trans('ip.my_companies'))
                    ->icon('heroicon-o-building-office-2')
                    ->url(fn () => MyCompanies::getUrl()),
                'profile' => fn (Action $action) => $action
                    ->label(trans('ip.edit_profile'))
                    ->icon('heroicon-o-user')
                    ->url(EditProfile::getUrl()),
                Action::make('settings')
                    ->label(trans('ip.settings'))
                    ->url(fn () => CompanySettings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn (): bool => auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false),
                Action::make('admin-panel')
                    ->label(trans('ip.admin_panel'))
                    ->url('/admin')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(UserRole::elevated()) ?? false),
                'logout' => fn (Action $action) => $action
                    ->label(trans('ip.logout'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle'),
            ]);

        return $companyPanel;
    }

    /**
     * Attaches a sidebar quick-create ("+") button to a resource's navigation
     * items, driven by the `data-quick-create-url` extra attribute consumed
     * by `resources/views/vendor/filament-panels/components/sidebar/item.blade.php`.
     *
     * Resources with a dedicated `create` page link straight to it; resources
     * that only create records via a modal action on their list page (no
     * `create` page registered) link to the index page with `?action=create`,
     * which Filament natively auto-mounts via its URL-bound action state.
     *
     * @param class-string<\Filament\Resources\Resource> $resourceClass
     *
     * @return array<NavigationItem>
     */
    private static function withQuickCreate(string $resourceClass): array
    {
        $hasCreatePage = array_key_exists('create', $resourceClass::getPages());

        return collect($resourceClass::getNavigationItems())
            ->map(fn (NavigationItem $item): NavigationItem => $item->extraAttributes([
                'data-quick-create-url' => $resourceClass::canCreate()
                    ? ($hasCreatePage
                        ? $resourceClass::getUrl('create')
                        : $resourceClass::getUrl('index', ['action' => 'create']))
                    : null,
            ], merge: true))
            ->all();
    }
}
