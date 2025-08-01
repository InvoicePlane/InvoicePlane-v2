<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractSeeder extends Seeder
{
    protected ?int $companyId = null;
    protected int  $count     = 0;

    /** Child classes must set these */
    protected string $label;
    protected int    $defaultCount = 10;

    public function run(): void
    {
        $this->resolveOptions();

        if (! $this->companyId) {
            $this->command->warn(static::class . ' skipped (no company id)');
            return;
        }

        $this->beforeSeed();
        $this->seedWithProgress();
        $this->afterSeed();
    }

    /* --------------------------------------------------------------------- */
    /*  Helpers                                                              */
    /* --------------------------------------------------------------------- */
    private function resolveOptions(): void
    {
        /** @var Command $cmd */
        $cmd = $this->command;

        $this->companyId = (int) ($this->parameters['company']
            ?? $cmd->option('company')
            ?? null);

        $this->count = (int) ($this->parameters['count']
            ?? $cmd->option('count')
            ?? $this->defaultCount);
    }

    private function seedWithProgress(): void
    {
        $bar = $this->progressBar($this->count);

        for ($i = 0; $i < $this->count; $i++) {
            $this->buildOne();
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("<fg=green>{$this->label}</>");
    }

    private function progressBar(int $max): ProgressBar
    {
        $bar = $this->command->getOutput()->createProgressBar($max);
        $bar->setFormat(" <comment>{$this->label}</comment> ▕%bar%▏ %current%/%max%");
        $bar->start();

        return $bar;
    }

    /* --------------------------------------------------------------------- */
    /*  Hooks                                                                */
    /* --------------------------------------------------------------------- */
    protected function beforeSeed(): void {}
    protected function afterSeed(): void {}

    abstract protected function buildOne(): void;

    /* --------------------------------------------------------------------- */

    protected function company(): Company
    {
        return Company::query()->findOrFail($this->companyId);
    }
}
