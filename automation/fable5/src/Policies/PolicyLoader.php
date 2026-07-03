<?php

declare(strict_types=1);

namespace Fable\Policies;

use Fable\Support\Paths;

final class PolicyLoader
{
    public function load(): array
    {
        $basePath = dirname(Paths::root()).'/.claude/fable5';

        return [
            'prd' => $this->loadFile($basePath.'/FABLE5_EXECUTION_PRD.md'),
            'skills' => $this->loadDirectory($basePath.'/skills'),
            'runtime' => $this->loadFile($basePath.'/runtime/overrides.md'),
            'repo' => $this->loadFile(dirname(Paths::root()).'/CLAUDE.md'),
        ];
    }

    private function loadFile(string $path): array
    {
        return file_exists($path)
            ? [file_get_contents($path)]
            : [];
    }

    private function loadDirectory(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = glob($path.'/*.md');

        return array_map(
            fn ($file) => file_get_contents($file),
            $files
        );
    }
}
