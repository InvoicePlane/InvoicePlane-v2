<?php

declare(strict_types=1);

namespace Fable\Policies;

use Fable\Support\Paths;

final class PolicyLoader
{
    /** @return array<string, array<int, string>> */
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

    /** @return array<int, string> */
    private function loadFile(string $path): array
    {
        if (file_exists($path)) {
            $content = file_get_contents($path);

            return is_string($content) ? [$content] : [];
        }

        return [];
    }

    /** @return array<int, string> */
    private function loadDirectory(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = glob($path.'/*.md') ?: [];
        $contents = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (is_string($content)) {
                $contents[] = $content;
            }
        }

        return $contents;
    }
}
