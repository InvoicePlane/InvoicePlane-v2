<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\UserRole;

/**
 * Shared shell for the tabular reports (#145): a date range (defaults to
 * the current month), an optional client filter, a read-only Filament
 * table with a summary line, and a CSV export of the filtered rows.
 * All queries are tenant-scoped through the BelongsToCompany global scope.
 */
abstract class BaseTabularReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?int $clientId = null;

    protected string $view = 'core::filament.company.pages.reports.tabular-report';

    /**
     * The filtered query behind both the table and the CSV export.
     */
    abstract public function reportQuery(): Builder;

    /**
     * @return array<int, \Filament\Tables\Columns\Column>
     */
    abstract protected function reportColumns(): array;

    /**
     * @return array<int, string>
     */
    abstract protected function csvHeaders(): array;

    /**
     * @return array<int, string|int|float>
     */
    abstract protected function csvRow($record): array;

    /**
     * One-line totals summary rendered under the table.
     */
    abstract public function summaryLine(): string;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            ...UserRole::elevated(),
            UserRole::CUSTOMER_ADMIN->value,
        ]) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('ip.reports');
    }

    public function mount(): void
    {
        $this->dateFrom ??= now()->startOfMonth()->toDateString();
        $this->dateTo ??= now()->endOfMonth()->toDateString();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->reportQuery())
            ->columns($this->reportColumns())
            ->paginated([25, 50, 100])
            ->headerActions([
                $this->exportCsvAction(),
            ]);
    }

    public function exportCsvAction(): Action
    {
        return Action::make('exportCsv')
            ->label(trans('ip.export_csv'))
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function () {
                $filename = static::getSlug() . '-' . now()->toDateString() . '.csv';

                return response()->streamDownload(function (): void {
                    $handle = fopen('php://output', 'wb');
                    fputcsv($handle, $this->csvHeaders());

                    foreach ($this->reportQuery()->get() as $record) {
                        fputcsv($handle, $this->csvRow($record));
                    }

                    fclose($handle);
                }, $filename, ['Content-Type' => 'text/csv']);
            });
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getClientOptions(): array
    {
        return Relation::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn (Relation $relation): array => ['id' => $relation->id, 'name' => $relation->company_name])
            ->all();
    }

    protected function dateRange(): array
    {
        return [
            $this->dateFrom ?? now()->startOfMonth()->toDateString(),
            $this->dateTo ?? now()->endOfMonth()->toDateString(),
        ];
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
