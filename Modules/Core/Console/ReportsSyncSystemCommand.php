<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Services\ReportTemplateStorage;

/**
 * Copies the shipped report templates from resources/report-templates into
 * the system scope of the report_templates disk. Idempotent — running it
 * again simply rewrites the system copies with the shipped versions.
 */
class ReportsSyncSystemCommand extends Command
{
    protected $signature = 'reports:sync-system';

    protected $description = 'Sync shipped report templates into the system template storage';

    public function handle(): int
    {
        $source = resource_path('report-templates');

        if ( ! File::isDirectory($source)) {
            $this->error("Source directory [{$source}] does not exist.");

            return self::FAILURE;
        }

        $disk   = Storage::disk(ReportTemplateStorage::DISK);
        $synced = 0;

        foreach (File::allFiles($source) as $file) {
            $disk->put(
                ReportTemplateStorage::SCOPE_SYSTEM . '/' . str_replace('\\', '/', $file->getRelativePathname()),
                $file->getContents(),
            );

            $synced++;
        }

        $this->info("Synced {$synced} report template file(s) into system storage.");

        return self::SUCCESS;
    }
}
