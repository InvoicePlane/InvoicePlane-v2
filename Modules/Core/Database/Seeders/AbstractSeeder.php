<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Project;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractSeeder extends Seeder
{
    protected ?int $companyId = null;

    protected int $count = 0;

    protected string $label;

    protected int $defaultCount = 10;

    protected array $parameters = [];

    abstract protected function buildOne(): void;

    public function run($company = null, $count = null): void
    {
        $this->companyId = $company;
        $this->count     = $count ?? $this->defaultCount;

        if ( ! $this->companyId) {
            $this->command->warn(static::class . ' skipped (no company id)');

            return;
        }

        $this->beforeSeed();
        $this->seedWithProgress();
        $this->afterSeed();
    }

    protected function beforeSeed(): void {}

    protected function afterSeed(): void {}

    protected function company(): Company
    {
        return Company::query()->findOrFail($this->companyId);
    }

    protected function findOrCreateCustomer(int $companyId): Relation
    {
        $customer = Relation::query()->where('company_id', $companyId)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first();

        if ( ! $customer) {
            $customer = Relation::factory()
                ->customer()
                ->state(['company_id' => $companyId])
                ->create();
        }

        return $customer;
    }

    protected function findOrCreateNumbering(?int $companyId): Numbering
    {
        /** @var Numbering|null $documentGroup */
        $documentGroup = Numbering::query()->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $documentGroup) {
            /** @var Numbering $documentGroup */
            $documentGroup = Numbering::factory()->state([
                'company_id' => $companyId,
            ])
                ->create();
        }

        return $documentGroup;
    }

    protected function findOrCreateExpenseCategory(?int $companyId): ExpenseCategory
    {
        $category = ExpenseCategory::query()->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $category) {
            $category = ExpenseCategory::factory()->state([
                'company_id' => $companyId,
            ])
                ->create();
        }

        return $category;
    }

    protected function findOrCreateInvoice(?int $companyId): Invoice
    {
        $invoice = Invoice::query()->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $invoice) {
            $documentGroup = $this->findOrCreateNumbering($companyId);

            $invoice = Invoice::factory()->state([
                'company_id'   => $this->companyId,
                'numbering_id' => $documentGroup->id,
            ])
                ->create();
        }

        return $invoice;
    }

    protected function findOrCreateProduct(int $companyId): Product
    {
        $product = Product::query()->where('company_id', $companyId)->inRandomOrder()->first();
        if ( ! $product) {
            $product = Product::factory()->state(['company_id' => $companyId])->create();
        }

        return $product;
    }

    protected function findOrCreateProductCategory(int $companyId): ProductCategory
    {
        $prodCat = ProductCategory::query()->where('company_id', $this->companyId)
            ->inRandomOrder()->first();

        if ( ! $prodCat) {
            $prodCat = ProductCategory::factory()->state(['company_id' => $companyId])->create();
        }

        return $prodCat;
    }

    protected function findOrCreateProductUnit(int $companyId): ProductUnit
    {
        $prodUnit = ProductUnit::query()->where('company_id', $this->companyId)
            ->inRandomOrder()->first();

        if ( ! $prodUnit) {
            $prodUnit = ProductUnit::factory()->state(['company_id' => $companyId])->create();
        }

        return $prodUnit;
    }

    protected function findOrCreateProject(int $companyId): Project
    {
        $project = Project::query()->where('company_id', $this->companyId)
            ->inRandomOrder()->first();

        if ( ! $project) {
            $project = Project::factory()->state(['company_id' => $companyId])->create();
        }

        return $project;
    }

    protected function findOrCreateProspect(int $companyId): Relation
    {
        $prospect = Relation::query()->where('company_id', $companyId)
            ->where('relation_type', RelationType::PROSPECT->value)
            ->inRandomOrder()
            ->first();

        if ( ! $prospect) {
            $prospect = Relation::factory()
                ->prospect()
                ->state(['company_id' => $companyId])
                ->create();
        }

        return $prospect;
    }

    protected function findOrCreateRelationOfType(int $companyId, RelationType $type): Relation
    {
        $relation = Relation::query()
            ->where('company_id', $companyId)
            ->where('relation_type', $type->value)
            ->inRandomOrder()
            ->first();

        if ($relation) {
            return $relation;
        }

        $factory = Relation::factory()->state(['company_id' => $companyId]);
        $factory = match ($type) {
            RelationType::CUSTOMER => $factory->customer(),
            RelationType::PROSPECT => $factory->prospect(),
            RelationType::VENDOR   => $factory->vendor(),
            default                => $factory,
        };

        return $factory->create();
    }

    protected function findOrCreateTaxRate(?int $companyId): TaxRate
    {
        $taxRate = TaxRate::query()->where('company_id', $this->companyId)
            ->inRandomOrder()->first();

        if ( ! $taxRate) {
            $taxRate = TaxRate::factory()->state(['company_id' => $companyId])->create();
        }

        return $taxRate;
    }

    protected function findOrCreateUser(int $companyId): User
    {
        $user = User::query()->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->inRandomOrder()
            ->first();

        if ( ! $user) {
            $user = User::factory()->create();
            $user->companies()->attach($companyId);
        }

        return $user;
    }

    private function seedWithProgress(): void
    {
        $bar = $this->command->getOutput()->createProgressBar($this->count);
        $bar->setFormat(" <comment>{$this->label}</comment> ▕%bar%▏ %current%/%max%");
        $bar->start();

        for ($i = 0; $i < $this->count; $i++) {
            $this->buildOne();
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
    }

    private function progressBar(int $max): ProgressBar
    {
        $bar = $this->command->getOutput()->createProgressBar($max);
        $bar->setFormat(" <comment>{$this->label}</comment> ▕%bar%▏ %current%/%max%");
        $bar->start();

        return $bar;
    }
}
