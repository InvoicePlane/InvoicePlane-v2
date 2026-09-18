<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Str;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\ExpenseCategoryResource;
use Modules\Products\Filament\Company\Resources\ProductCategories\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use PHPUnit\Framework\Attributes\Test;

/**
 * Covers GH #616 — Product Units, Product Families and Expense Categories
 * should no longer appear in the company panel sidebar, while remaining
 * fully reachable via direct URL.
 */
class CompanyPanelNavigationVisibilityTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_flags_product_units_product_categories_and_expense_categories_as_not_registering_navigation(): void
    {
        /* Act & Assert */
        $this->assertFalse(ProductUnitResource::shouldRegisterNavigation());
        $this->assertFalse(ProductCategoryResource::shouldRegisterNavigation());
        $this->assertFalse(ExpenseCategoryResource::shouldRegisterNavigation());
    }

    #[Test]
    public function it_omits_product_units_and_product_families_from_the_resources_navigation_group(): void
    {
        /* Arrange */
        Filament::setCurrentPanel(Filament::getPanel('company'));
        $this->actingAs($this->user);
        request()->merge(['tenant' => Str::lower($this->company->search_code)]);

        /* Act */
        $groups = Filament::getPanel('company')->getNavigation();

        /** @var NavigationGroup $resourcesGroup */
        $resourcesGroup = collect($groups)->first(
            fn (NavigationGroup $group): bool => $group->getLabel() === 'Resources'
        );

        $labels = collect($resourcesGroup->getItems())
            ->map(fn ($item) => $item->getLabel())
            ->all();

        /* Assert */
        $this->assertNotContains(trans('ip.product_units'), $labels);
        $this->assertNotContains(trans('ip.product_families'), $labels);

        // Sanity check: the fix shouldn't hide everything in the group.
        $this->assertContains(trans('ip.products'), $labels);
    }

    #[Test]
    public function it_omits_expense_categories_from_the_expenses_navigation_group(): void
    {
        /* Arrange */
        Filament::setCurrentPanel(Filament::getPanel('company'));
        $this->actingAs($this->user);
        request()->merge(['tenant' => Str::lower($this->company->search_code)]);

        /* Act */
        $groups = Filament::getPanel('company')->getNavigation();

        /** @var NavigationGroup $expensesGroup */
        $expensesGroup = collect($groups)->first(
            fn (NavigationGroup $group): bool => $group->getLabel() === 'Expenses'
        );

        $labels = collect($expensesGroup->getItems())
            ->map(fn ($item) => $item->getLabel())
            ->all();

        /* Assert */
        $this->assertNotContains(trans('ip.expense_categories'), $labels);

        // Sanity check: the fix shouldn't hide everything in the group.
        $this->assertContains(trans('ip.expenses'), $labels);
    }
}
