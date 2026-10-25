<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class DesignerPreviewLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function it_translates_sample_text_while_preserving_sample_identifiers(): void
    {
        app()->setLocale('de');
        app('translator')->addLines([
            'designer.sample_project' => 'Beispielprojekt',
            'designer.sample_task'    => 'Beispielaufgabe',
            'ip.task_description'     => 'Beschreibung der Aufgabe',
        ], 'de');

        $project = view('mason.bricks.header-project.preview', ['config' => []])->render();
        $tasks   = view('mason.bricks.detail-tasks.preview', ['config' => []])->render();

        $this->assertStringContainsString('Beispielprojekt', $project);
        $this->assertStringContainsString('PROJECT-001', $project);
        $this->assertStringContainsString('Beispielaufgabe', $tasks);
        $this->assertStringContainsString('Beschreibung der Aufgabe', $tasks);
        $this->assertStringContainsString('TASK-001', $tasks);
    }
}
