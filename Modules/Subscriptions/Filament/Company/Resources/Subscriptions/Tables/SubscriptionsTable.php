<?php

namespace Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\CancellationType;
use Modules\Subscriptions\Enums\SubscriptionStatus;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Services\SubscriptionService;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Subscription #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('customer.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionStatus ? $state->label() : SubscriptionStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn ($state) => $state instanceof SubscriptionStatus ? $state->color() : SubscriptionStatus::tryFrom($state)?->color() ?? 'gray')
                    ->icon(fn ($state) => $state instanceof SubscriptionStatus ? $state->badgeIcon() : SubscriptionStatus::tryFrom($state)?->badgeIcon() ?? 'heroicon-o-minus-circle')
                    ->sortable(),

                TextColumn::make('billing_interval')
                    ->label('Billing Cycle')
                    ->formatStateUsing(function ($state, Subscription $record) {
                        if ($record->billing_interval === BillingInterval::CUSTOM) {
                            return "Every {$record->interval_count} {$record->interval_unit?->value}(s)";
                        }

                        return $record->billing_interval?->label() ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('current_period_ends_at')
                    ->label('Next Billing Date')
                    ->dateTime('M d, Y')
                    ->sortable(),

                IconColumn::make('cancel_at_period_end')
                    ->label('Pending Cancel')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        collect(SubscriptionStatus::cases())
                            ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                            ->toArray()
                    ),

                SelectFilter::make('billing_interval')
                    ->options(
                        collect(BillingInterval::cases())
                            ->mapWithKeys(fn ($i) => [$i->value => $i->label()])
                            ->toArray()
                    ),
            ])
            ->actions([
                EditAction::make(),

                Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('gray')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::ACTIVE || $record->status === SubscriptionStatus::TRIALING)
                    ->form([
                        DateTimePicker::make('resume_at')
                            ->label('Auto-Resume At (Optional)')
                            ->hint('Leave empty for manual resume'),
                    ])
                    ->action(function (Subscription $record, array $data, SubscriptionService $service) {
                        $service->pause($record, isset($data['resume_at']) ? \Carbon\Carbon::parse($data['resume_at']) : null);
                        Notification::make()->title('Subscription Paused')->warning()->send();
                    }),

                Action::make('resume')
                    ->label('Resume')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::PAUSED)
                    ->action(function (Subscription $record, SubscriptionService $service) {
                        $service->resume($record);
                        Notification::make()->title('Subscription Resumed')->success()->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscription $record) => $record->status !== SubscriptionStatus::CANCELED && $record->status !== SubscriptionStatus::EXPIRED)
                    ->form([
                        Select::make('cancellation_type')
                            ->label('Cancellation Option')
                            ->options([
                                CancellationType::AT_PERIOD_END->value => 'Cancel at End of Billing Period',
                                CancellationType::IMMEDIATE->value     => 'Cancel Immediately',
                            ])
                            ->default(CancellationType::AT_PERIOD_END->value)
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data, SubscriptionService $service) {
                        if ($data['cancellation_type'] === CancellationType::IMMEDIATE->value) {
                            $service->cancelImmediately($record);
                            Notification::make()->title('Subscription Canceled Immediately')->danger()->send();
                        } else {
                            $service->cancelAtPeriodEnd($record);
                            Notification::make()->title('Subscription set to cancel at period end')->warning()->send();
                        }
                    }),

                Action::make('process_billing')
                    ->label('Bill Now')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::ACTIVE || $record->status === SubscriptionStatus::TRIALING)
                    ->requiresConfirmation()
                    ->action(function (Subscription $record, SubscriptionService $service) {
                        $invoice = $service->processBillingCycle($record);
                        if ($invoice) {
                            Notification::make()
                                ->title('Invoice Generated Successfully')
                                ->body("Invoice #{$invoice->invoice_number} created for this subscription.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()->title('Could not bill subscription')->warning()->send();
                        }
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
