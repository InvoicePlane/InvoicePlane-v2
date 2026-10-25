<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Pages\ImportV1Page;
use Modules\Core\Models\Company;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Core\Services\Migration\V1MigrationManager;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ImportDesignerLocalizationTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('de');
        app('translator')->addLines([
            'import.navigation'         => 'Prüfimport aus v1',
            'import.title'              => 'Prüfassistent für den Import',
            'import.source_target'      => 'Prüfquelle und Ziel',
            'import.target_company'     => 'Prüfzielfirma',
            'import.host'               => 'Prüfhost',
            'import.analyze_source'     => 'Prüfquelle analysieren',
            'import.mismatches'         => 'Abweichungsprüfung: :count',
            'import.invariant_mismatch' => ':type #:number (:field): Soll :expected, Ist :actual',
            'import.entities.invoices'  => 'Prüfrechnungen und Posten',
            'import.entities.products'  => 'Prüfprodukte und Kategorien',
            'designer.title'            => 'Prüfberichtseditor',
            'designer.drop_blocks'      => 'Prüfblöcke hier ablegen',
            'designer.configure_fields' => 'Prüffelder konfigurieren',
            'designer.band_header'      => 'Pruefkopfbereich',
        ], 'de');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function import_navigation_and_title_follow_the_current_locale(): void
    {
        $this->assertSame('Prüfimport aus v1', ImportV1Page::getNavigationLabel());
        $this->assertSame('Prüfassistent für den Import', (new ImportV1Page())->getTitle());
    }

    #[Test]
    public function import_form_renders_translated_labels_without_changing_source_identifiers(): void
    {
        Livewire::test(ImportLocalizationFixture::class)
            ->assertSee('Prüfquelle und Ziel')
            ->assertSee('Prüfzielfirma')
            ->assertSee('Prüfquelle analysieren')
            ->assertSeeHtml('value="sql_file"')
            ->assertSeeHtml('value="database"')
            ->assertSeeHtml('placeholder="ip_"')
            ->set('sourceType', 'database')
            ->assertSee('Prüfhost')
            ->assertSeeHtml('wire:model="dbHost"')
            ->assertSeeHtml('wire:model="dbDatabase"')
            ->assertSeeHtml('wire:model="dbPassword"');
    }

    #[Test]
    public function import_results_translate_messages_while_preserving_diagnostic_values(): void
    {
        Livewire::test(ImportLocalizationFixture::class)
            ->set('currentStep', 3)
            ->set('migrationResult', [
                'batch_id'             => 'batch-original',
                'success'              => false,
                'results'              => [],
                'financial_invariants' => [
                    'passed'       => false,
                    'failed_count' => 1,
                    'mismatches'   => [[
                        'type'     => 'invoice', 'number' => 'INV-1', 'field' => 'balance',
                        'expected' => '120.00', 'actual' => '100.00',
                    ]],
                ],
            ])
            ->assertSee('Abweichungsprüfung: 1')
            ->assertSee('Soll 120.00, Ist 100.00')
            ->assertSee('INV-1')
            ->assertSee('balance')
            ->assertSee('batch-original');
    }

    #[Test]
    public function inspection_entity_labels_are_translated_by_the_migration_service(): void
    {
        $context = new MigrationContext(new Company());
        $context->setSourceTables([]);
        $inspection = app(V1MigrationManager::class)->inspect($context);

        $this->assertSame('Prüfrechnungen und Posten', $inspection['entities']['invoices']['label']);
        $this->assertSame('Prüfprodukte und Kategorien', $inspection['entities']['products']['label']);
        $this->assertSame(0, $inspection['total_source_count']);
    }

    #[Test]
    public function report_designer_renders_translations_in_text_attributes_and_javascript(): void
    {
        // This legacy view's production service is absent; only supply its empty data boundary.
        app()->instance('Modules\\Core\\Services\\ReportTemplateService', new class () {
            public function getSystemBlocks(): array
            {
                return [];
            }
        });

        Livewire::test(DesignerLocalizationFixture::class)
            ->assertSee('Prüfberichtseditor')
            ->assertSee('Prüfblöcke hier ablegen')
            ->assertSeeHtml('title="Prüffelder konfigurieren"')
            ->assertSeeHtml('Pruefkopfbereich')
            ->assertSeeHtml('group_header');
    }
}

class ImportLocalizationFixture extends ImportV1Page
{
    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(): void
    {
        app()->setLocale('de');
    }

    public function hydrate(): void
    {
        app()->setLocale('de');
    }

    public function getCompaniesProperty(): Collection
    {
        return collect();
    }
}

class DesignerLocalizationFixture extends Page
{
    public array $blocks = [];

    protected string $view = 'core::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function mount(): void
    {
        app()->setLocale('de');
    }

    public function getResource(): string
    {
        return DesignerResourceLocalizationFixture::class;
    }
}

class DesignerResourceLocalizationFixture
{
    public static function getUrl(string $page): string
    {
        return '/designer-localization-test';
    }
}
