<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TempFileCleanupHelper
{
    public function deleteViewCache(): void
    {
        foreach (File::files(storage_path('framework/views')) as $file) {
            try {
                unlink($file);
            } catch (Exception $e) {
                Log::info('Could not delete ' . $file);
            }
        }
    }

    public function deleteTempFiles(): void
    {
        foreach (File::files(storage_path()) as $file) {
            if (in_array(File::extension($file), ['pdf', 'csv'])) {
                try {
                    unlink($file);
                } catch (Exception $e) {
                    Log::info('Could not delete ' . $file);
                }
            }
        }
    }
}
