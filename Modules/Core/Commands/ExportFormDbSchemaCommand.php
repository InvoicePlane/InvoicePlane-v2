<?php

namespace Modules\Core\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema as DbSchema;
use Modules\Core\Support\FormDbGapKnownExceptions;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Frontend half of the mind-the-gap audit. Dumps, per registered Filament
 * resource in every panel, its DB table's real column constraints and
 * unique indexes as JSON — the ground truth that testrunner's
 * find-form-gaps.js cross-checks against the *actually rendered* DOM form
 * (real HTML `required`/`maxlength` attributes), the same way
 * FormDbConstraintAuditTest.php cross-checks the live PHP form schema.
 *
 * Pure DB/Filament-metadata introspection — no HTTP request, Livewire
 * component, or tenant context needed, so this runs safely from a plain
 * artisan invocation:
 *
 *   php artisan mind-the-gap:export-schema > schema.json
 */
#[AsCommand(name: 'mind-the-gap:export-schema')]
class ExportFormDbSchemaCommand extends Command
{
    protected $description = 'Export DB column/index constraints for every Filament resource, for the frontend half of the mind-the-gap form/DB audit';

    protected $signature = 'mind-the-gap:export-schema';

    public function handle(): int
    {
        $resources = [];

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resourceClass) {
                $entry = $this->describeResource($resourceClass, $panel);

                if ($entry !== null) {
                    $resources[] = $entry;
                }
            }
        }

        $this->line((string) json_encode([
            'generatedAt' => now()->toIso8601String(),
            'resources'   => $resources,
            'knownGaps'   => FormDbGapKnownExceptions::KNOWN_GAPS,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return static::SUCCESS;
    }

    /**
     * @return array{resourceClass: string, panel: string, table: string, slug: string, columns: array<int, array<string, mixed>>, uniqueIndexes: array<int, array<string, mixed>>}|null
     */
    private function describeResource(string $resourceClass, Panel $panel): ?array
    {
        if ( ! method_exists($resourceClass, 'getModel')) {
            return null;
        }

        $model = $resourceClass::getModel();
        $table = (new $model())->getTable();

        if ( ! DbSchema::hasTable($table)) {
            return null;
        }

        $columns = collect(DbSchema::getColumns($table))
            ->map(fn (array $column) => [
                'name'           => $column['name'],
                'nullable'       => $column['nullable'],
                'default'        => $column['default'],
                'type'           => $column['type'],
                'type_name'      => $column['type_name'],
                'auto_increment' => $column['auto_increment'],
            ])
            ->values()
            ->all();

        $uniqueIndexes = collect(DbSchema::getIndexes($table))
            ->filter(fn (array $index) => $index['unique'] && ! $index['primary'])
            ->map(fn (array $index) => [
                'name'    => $index['name'],
                'columns' => $index['columns'],
            ])
            ->values()
            ->all();

        return [
            'resourceClass' => $resourceClass,
            'panel'         => $panel->getId(),
            'table'         => $table,
            'slug'          => $resourceClass::getSlug($panel),
            'columns'       => $columns,
            'uniqueIndexes' => $uniqueIndexes,
        ];
    }
}
