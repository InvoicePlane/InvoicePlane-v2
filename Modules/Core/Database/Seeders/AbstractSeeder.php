<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
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
        $this->count     = $count !== null ? (int) $count : $this->defaultCount;

        if ( ! $this->companyId) {
            $this->command->warn('[DEBUG] Skipped: ' . static::class . ' (no company id)');

            return;
        }

        $this->beforeSeed();
        $this->seedWithProgress();
        $this->afterSeed();
    }

    protected function beforeSeed(): void {}

    protected function afterSeed(): void {}

    protected function company(): ?Company
    {
        return Company::query()->findOrFail($this->companyId);
    }

    protected function findOrCreateProspect(int $companyId): Relation
    {
        $prospect = Relation::query()
            ->where('company_id', $companyId)
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

    private function seedWithProgress(): void
    {
        $bar = $this->progressBar($this->count);
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
