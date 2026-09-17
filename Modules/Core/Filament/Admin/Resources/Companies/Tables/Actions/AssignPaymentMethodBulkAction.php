<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Company;
use Modules\Payments\Enums\PaymentMethod;

class AssignPaymentMethodBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('ip.assign_payment_method'));

        $this->icon(Heroicon::OutlinedCreditCard);

        $this->schema([
            Select::make('payment_method')
                ->label(trans('ip.payment_method'))
                ->options(fn () => static::getPaymentMethodOptions())
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $attachedCount = 0;

            /** @var Company $company */
            foreach ($records as $company) {
                $paymentMethod = $company->paymentMethods()->firstOrCreate([
                    'payment_method' => $data['payment_method'],
                ]);

                if ($paymentMethod->wasRecentlyCreated) {
                    $attachedCount++;
                }
            }

            Notification::make()
                ->title(trans_choice('ip.payment_method_assigned', $attachedCount, ['count' => $attachedCount]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignPaymentMethod';
    }

    /**
     * @return array<string, string>
     */
    public static function getPaymentMethodOptions(): array
    {
        return collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method) => [$method->value => $method->label()])
            ->all();
    }
}
