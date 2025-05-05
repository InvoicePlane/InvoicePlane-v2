<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\CreateExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\ListExpenseCategories;
use Modules\Expenses\Models\ExpenseCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseCategoryResource::class)]

class ExpenseCategoriesTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_expense_categories(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        ExpenseCategory::factory()->create([
            'company_id'    => $company->id,
            'category_name' => 'Example',
        ]);

        Livewire::test(ListExpenseCategories::class)
            ->assertSee('Example');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense_category(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'    => $company->id,
            'category_name' => 'Example',
        ];

        Livewire::test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('expense_categories', [
            'company_id'    => $company->id,
            'category_name' => 'Example',
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "category_name": "Example"
     * }
     */
    public function it_fails_to_create_expense_category_without_category_name(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id' => $company->id,
        ];

        Livewire::test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['category_name' => 'required']);
    }
}
