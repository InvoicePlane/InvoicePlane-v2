<?php

declare(strict_types=1);

final class Fable5PolicyLoader
{
    public function load(): array
    {
        return [
            'prd' => $this->loadFile('.claude/fable5/FABLE5_EXECUTION_PRD.md'),
            'skills' => $this->loadDirectory('.claude/fable5/skills'),
            'runtime' => $this->loadFile('.claude/fable5/runtime/overrides.md'),
            'repo' => $this->loadFile('CLAUDE.md'),
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
        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.md');

        return array_map(
            fn ($file) => file_get_contents($file),
            $files
        );
    }
}
