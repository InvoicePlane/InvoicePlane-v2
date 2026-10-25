<?php

namespace Modules\Core\Tests\Fixtures;

use Livewire\Component;

class LocaleProbe extends Component
{
    public string $localeDuringAction = '';

    public function captureLocale(): void
    {
        $this->localeDuringAction = app()->getLocale();
    }

    public function render(): string
    {
        return '<div>Action: {{ $localeDuringAction }}; Render: {{ app()->getLocale() }}</div>';
    }
}
