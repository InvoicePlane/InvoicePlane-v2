<?php

namespace Modules\Core\Filament\Admin\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Livewire\WithFileUploads;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Core\Services\Migration\V1MigrationManager;
use Throwable;

class ImportV1Page extends Page
{
    use WithFileUploads;

    public int $currentStep = 1;

    // Step 1 Form Data
    public ?int $selectedCompanyId = null;

    public string $sourceType = 'sql_file'; // 'sql_file' or 'database'

    public string $tablePrefix = 'ip_';

    public $sqlFile = null;

    // Direct DB connection fields
    public string $dbHost = '127.0.0.1';

    public string $dbPort = '3306';

    public string $dbDatabase = 'invoiceplane_v1';

    public string $dbUsername = 'root';

    public string $dbPassword = '';

    // Results / State
    public ?array $connectionTestResult = null;

    public ?array $inspectionResult = null;

    public ?array $migrationResult = null;

    public ?array $rollbackResult = null;

    public array $executionLogs = [];

    public bool $isExecuting = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected string $view = 'core::filament.admin.pages.import-v1';

    protected static ?string $navigationLabel = 'Import from v1';

    protected static ?string $title = 'Guided InvoicePlane v1 Migration';

    protected static ?int $navigationSort = 50;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->hasRole(UserRole::ADMIN->value));
    }

    public function mount(): void
    {
        $firstCompany = Company::first();
        if ($firstCompany) {
            $this->selectedCompanyId = $firstCompany->id;
        }
    }

    public function getCompaniesProperty(): Collection
    {
        return Company::query()->orderBy('name')->get();
    }

    public function testConnection(): void
    {
        $manager = app(V1MigrationManager::class);
        $res     = $manager->testDbConnection([
            'host'     => $this->dbHost,
            'port'     => $this->dbPort,
            'database' => $this->dbDatabase,
            'username' => $this->dbUsername,
            'password' => $this->dbPassword,
        ], $this->tablePrefix);

        $this->connectionTestResult = $res;

        if ($res['success']) {
            Notification::make()->title('Connection Successful')->body($res['message'])->success()->send();
        } else {
            Notification::make()->title('Connection Failed')->body($res['message'])->danger()->send();
        }
    }

    public function proceedToInspection(): void
    {
        if ( ! $this->selectedCompanyId) {
            Notification::make()->title('Please select a target company')->warning()->send();

            return;
        }

        $company = Company::findOrFail($this->selectedCompanyId);
        $manager = app(V1MigrationManager::class);

        try {
            $context                = $this->buildContext($company, true);
            $this->inspectionResult = $manager->inspect($context);
            $this->currentStep      = 2;

            Notification::make()->title('Source data analyzed successfully')->success()->send();
        } catch (Throwable $e) {
            Notification::make()->title('Inspection Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function runMigration(): void
    {
        if ( ! $this->selectedCompanyId) {
            return;
        }

        $company             = Company::findOrFail($this->selectedCompanyId);
        $manager             = app(V1MigrationManager::class);
        $this->isExecuting   = true;
        $this->executionLogs = [];

        try {
            $context = $this->buildContext($company, false);
            $result  = $manager->run($context, function ($status, $message) {
                $this->executionLogs[] = '[' . date('H:i:s') . '] ' . $message;
            });

            $this->migrationResult = $result;
            $this->currentStep     = 3;

            if ($result['success']) {
                Notification::make()->title('Migration completed successfully')->success()->send();
            } else {
                Notification::make()->title('Migration completed with errors')->warning()->send();
            }
        } catch (Throwable $e) {
            Notification::make()->title('Migration Failed')->body($e->getMessage())->danger()->send();
        } finally {
            $this->isExecuting = false;
        }
    }

    public function rollback(): void
    {
        if ( ! $this->migrationResult || empty($this->migrationResult['batch_id'])) {
            return;
        }

        $company = Company::findOrFail($this->selectedCompanyId);
        $manager = app(V1MigrationManager::class);

        try {
            $context              = new MigrationContext($company, auth()->user(), false, $this->migrationResult['batch_id']);
            $res                  = $manager->rollback($context);
            $this->rollbackResult = $res;
            Notification::make()->title('Batch rollback completed')->info()->send();
        } catch (Throwable $e) {
            Notification::make()->title('Rollback Failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function resetWizard(): void
    {
        $this->currentStep          = 1;
        $this->inspectionResult     = null;
        $this->migrationResult      = null;
        $this->rollbackResult       = null;
        $this->connectionTestResult = null;
        $this->executionLogs        = [];
        $this->sqlFile              = null;
    }

    protected function buildContext(Company $company, bool $dryRun): MigrationContext
    {
        $manager = app(V1MigrationManager::class);

        if ($this->sourceType === 'sql_file') {
            if ( ! $this->sqlFile) {
                throw new InvalidArgumentException('Please upload a valid SQL dump file.');
            }

            $filePath = $this->sqlFile->getRealPath();

            return $manager->createContextFromSql($filePath, $company, auth()->user(), $dryRun, $this->tablePrefix);
        }

        return $manager->createContextFromDb([
            'host'     => $this->dbHost,
            'port'     => $this->dbPort,
            'database' => $this->dbDatabase,
            'username' => $this->dbUsername,
            'password' => $this->dbPassword,
        ], $company, auth()->user(), $dryRun, $this->tablePrefix);
    }
}
