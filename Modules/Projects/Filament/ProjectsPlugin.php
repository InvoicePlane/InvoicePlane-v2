<?php

namespace Modules\Projects\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Projects\Filament\Resources\ProjectResource;
use Modules\Projects\Filament\Resources\TaskResource;

class ProjectsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Projects';
    }

    public function getId(): string
    {
        return 'projects';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ProjectResource::class,
                TaskResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
