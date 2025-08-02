<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Products\Models\Product;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractSeeder extends Seeder
{
    protected ?int $companyId = null;
    protected int $count = 0;
    protected string $label;
    protected int $defaultCount = 10;

    abstract protected function buildOne(): void;

    public function run($company = null, $count = null): void
    {
        $this->companyId = $company ? (int) $company : null;
        $this->count = $count ? (int) $count : $this->defaultCount;

        if (! $this->companyId) {
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
        return Company::findOrFail($this->companyId);
    }

    // ---- Reusable Helpers ----

    protected function findOrCreateCustomer(int $companyId): Relation
    {
        $customer = Relation::query()->where('company_id', $companyId)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first();

        if (! $customer) {
            $customer = Relation::factory()
                ->customer()
                ->state(['company_id' => $companyId])
                ->create();
        }
        return $customer;
    }

    protected function findOrCreateProspect(int $companyId): Relation
    {
        $prospect = Relation::query()->where('company_id', $companyId)
            ->where('relation_type', RelationType::PROSPECT->value)
            ->inRandomOrder()
            ->first();

        if (! $prospect) {
            $prospect = Relation::factory()
                ->prospect()
                ->state(['company_id' => $companyId])
                ->create();
        }
        return $prospect;
    }

    protected function findOrCreateUser(int $companyId): User
    {
        $user = User::query()->whereHas('companies', fn($q) => $q->where('companies.id', $companyId))
            ->inRandomOrder()
            ->first();

        if (! $user) {
            $user = User::factory()->create();
            $user->companies()->attach($companyId);
        }
        return $user;
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

    protected function findOrCreateProduct(int $companyId): Product
    {
        $product = Product::query()->where('company_id', $companyId)->inRandomOrder()->first();
        if (!$product) {
            $product = Product::factory()->state(['company_id' => $companyId])->create();
        }
        return $product;
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
