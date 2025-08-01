<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractSeeder extends Seeder
{
    protected ?int $companyId = null;

    protected int  $count = 0;

    /** Child classes must set these */
    protected string $label;

    protected int    $defaultCount = 10;

    private array $parameters;

    abstract protected function buildOne(): void;

    public function run(array $parameters = []): void
    {
        $this->parameters = $parameters;
        $this->resolveOptions();

        if ( ! $this->companyId) {
            $this->command->warn(static::class . ' skipped (no company id)');

            return;
        }

        $this->beforeSeed();
        $this->seedWithProgress();
        $this->afterSeed();
    }

    /* --------------------------------------------------------------------- */
    /*  Hooks                                                                */
    /* --------------------------------------------------------------------- */
    protected function beforeSeed(): void {}

    protected function afterSeed(): void {}

    /* --------------------------------------------------------------------- */

    protected function company(): Company
    {
        return Company::query()->findOrFail($this->companyId);
    }

    /* --------------------------------------------------------------------- */
    /*  Helpers                                                              */
    /* --------------------------------------------------------------------- */
    private function resolveOptions(): void
    {
        /** @var \Illuminate\Console\Command $cmd */
        $cmd = $this->command;

        $def = $cmd->getDefinition();

        $this->companyId = (int) ($this->parameters['company']
            ?? ($def->hasOption('company') ? $cmd->option('company') : null)
            ?? null);

        $this->count = (int) ($this->parameters['count']
            ?? ($def->hasOption('count') ? $cmd->option('count') : null)
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
        $this->command->newLine(2);
        $style = new OutputFormatterStyle('blue', null, ['bold']);
        $this->command->getOutput()->getFormatter()->setStyle('brand', $style);
        $this->command->line('<brand>InvoicePlane</brand>');
        $this->command->newLine();
    }

    private function progressBar(int $max): ProgressBar
    {
        $bar = $this->command->getOutput()->createProgressBar($max);
        $bar->setFormat(" <comment>{$this->label}</comment> ▕%bar%▏ %current%/%max%");
        $bar->start();

        return $bar;
    }
}
