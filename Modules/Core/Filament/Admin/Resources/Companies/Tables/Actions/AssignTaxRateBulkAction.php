<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;

class AssignTaxRateBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('ip.assign_tax_rate'));

        $this->icon(Heroicon::OutlinedReceiptPercent);

        $this->schema([
            Select::make('tax_rate_id')
                ->label(trans('ip.tax_rate'))
                /**
                /*
                 * Tax rates are scoped to their owning company by the
                 * BelongsToCompany global scope, but an admin assigning
                 * tax rates across companies needs to pick from all of
                 * them regardless of the current session/tenant company.
                */
                 */
                ->options(fn () => static::getTaxRateOptions())
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $attachedCount = 0;

            /** @var Company $company */
            foreach ($records as $company) {
                $result = $company->assignedTaxRates()->syncWithoutDetaching([$data['tax_rate_id']]);
                $attachedCount += count($result['attached']);
            }

            Notification::make()
                ->title(trans_choice('ip.tax_rate_assigned', $attachedCount, ['count' => $attachedCount]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignTaxRate';
    }

    /**
     * @return array<int, string>
     */
    public static function getTaxRateOptions(): array
    {
        return TaxRate::withoutGlobalScopes()
            ->with('company:id,name')
            ->get()
            ->mapWithKeys(fn (TaxRate $taxRate) => [
                $taxRate->id => sprintf(
                    '%s (%s)',
                    $taxRate->name,
                    $taxRate->company?->name ?? trans('ip.company')
                ),
            ])
            ->all();
    }
}
