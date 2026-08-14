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
                    ->label(trans('ip.subscription_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label(trans('ip.subscription_title'))
                    ->searchable()
                    ->limit(25),

                TextColumn::make('customer.company_name')
                    ->label(trans('ip.subscription_client'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(trans('ip.subscription_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionStatus ? $state->label() : SubscriptionStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn ($state) => $state instanceof SubscriptionStatus ? $state->color() : SubscriptionStatus::tryFrom($state)?->color() ?? 'gray')
                    ->icon(fn ($state) => $state instanceof SubscriptionStatus ? $state->badgeIcon() : SubscriptionStatus::tryFrom($state)?->badgeIcon() ?? 'heroicon-o-minus-circle')
                    ->sortable(),

                TextColumn::make('billing_interval')
                    ->label(trans('ip.billing_cycle'))
                    ->formatStateUsing(function ($state, Subscription $record) {
                        if ($record->billing_interval === BillingInterval::CUSTOM) {
                            return "Every {$record->interval_count} {$record->interval_unit?->value}(s)";
                        }

                        return $record->billing_interval?->label() ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('price')
                    ->label(trans('ip.recurring_price'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('current_period_ends_at')
                    ->label(trans('ip.subscription_next_billing_date'))
                    ->dateTime('M d, Y')
                    ->sortable(),

                IconColumn::make('cancel_at_period_end')
                    ->label(trans('ip.subscription_pending_cancel'))
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
                    ->label(trans('ip.subscription_pause'))
                    ->icon('heroicon-o-pause')
                    ->color('gray')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::ACTIVE || $record->status === SubscriptionStatus::TRIALING)
                    ->form([
                        DateTimePicker::make('resume_at')
                            ->label(trans('ip.subscription_auto_resume_at'))
                            ->hint(trans('ip.subscription_auto_resume_hint')),
                    ])
                    ->action(function (Subscription $record, array $data, SubscriptionService $service) {
                        $service->pause($record, isset($data['resume_at']) ? \Carbon\Carbon::parse($data['resume_at']) : null);
                        Notification::make()->title(trans('ip.subscription_paused_notification'))->warning()->send();
                    }),

                Action::make('resume')
                    ->label(trans('ip.subscription_resume'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::PAUSED)
                    ->action(function (Subscription $record, SubscriptionService $service) {
                        $service->resume($record);
                        Notification::make()->title(trans('ip.subscription_resumed_notification'))->success()->send();
                    }),

                Action::make('cancel')
                    ->label(trans('ip.subscription_cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscription $record) => $record->status !== SubscriptionStatus::CANCELED && $record->status !== SubscriptionStatus::EXPIRED)
                    ->form([
                        Select::make('cancellation_type')
                            ->label(trans('ip.subscription_cancellation_option'))
                            ->options([
                                CancellationType::AT_PERIOD_END->value => trans('ip.subscription_cancel_at_period_end'),
                                CancellationType::IMMEDIATE->value     => trans('ip.subscription_cancel_immediately'),
                            ])
                            ->default(CancellationType::AT_PERIOD_END->value)
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data, SubscriptionService $service) {
                        if ($data['cancellation_type'] === CancellationType::IMMEDIATE->value) {
                            $service->cancelImmediately($record);
                            Notification::make()->title(trans('ip.subscription_canceled_immediately_notification'))->danger()->send();
                        } else {
                            $service->cancelAtPeriodEnd($record);
                            Notification::make()->title(trans('ip.subscription_cancel_at_period_end_notification'))->warning()->send();
                        }
                    }),

                Action::make('process_billing')
                    ->label(trans('ip.subscription_bill_now'))
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::ACTIVE || $record->status === SubscriptionStatus::TRIALING)
                    ->requiresConfirmation()
                    ->action(function (Subscription $record, SubscriptionService $service) {
                        $invoice = $service->processBillingCycle($record);
                        if ($invoice) {
                            Notification::make()
                                ->title(trans('ip.subscription_invoice_generated_title'))
                                ->body("Invoice #{$invoice->invoice_number} created for this subscription.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()->title(trans('ip.subscription_billing_failed_notification'))->warning()->send();
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
