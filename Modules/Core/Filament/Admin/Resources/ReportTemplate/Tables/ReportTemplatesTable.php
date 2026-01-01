<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplateResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\ReportTemplateService;

class ReportTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('ip.id'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->label(trans('ip.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('template_type')
                    ->label(trans('ip.type'))
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_system')
                    ->label(trans('ip.system'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(trans('ip.active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(trans('ip.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('template_type')
                    ->label(trans('ip.template_type'))
                    ->options(
                        collect(ReportTemplateType::cases())
                            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
                    ),
                TernaryFilter::make('is_active')
                    ->label(trans('ip.active'))
                    ->nullable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon(Heroicon::OutlinedEye),
                    EditAction::make()
                        ->icon(Heroicon::OutlinedPencil)
                        ->action(function (ReportTemplate $record, array $data) {
                            $blocks = $data['blocks'] ?? [];
                            app(ReportTemplateService::class)->updateTemplate($record, $blocks);
                        })
                        ->modalWidth('full')
                        ->visible(fn (ReportTemplate $record) => ! $record->is_system),
                    /* @phpstan-ignore-next-line */
                    Action::make('design')
                        ->label(trans('ip.design'))
                        ->icon(Heroicon::OutlinedPaintBrush)
                        ->url(fn (ReportTemplate $record) => route('filament.admin.resources.report-templates.design', ['record' => $record->id]))
                        ->visible(fn (ReportTemplate $record) => ! $record->is_system),
                    /* @phpstan-ignore-next-line */
                    Action::make('clone')
                        ->label(trans('ip.clone'))
                        ->icon(Heroicon::OutlinedDocumentDuplicate)
                        ->requiresConfirmation()
                        ->action(function (ReportTemplate $record) {
                            $service = app(ReportTemplateService::class);
                            $blocks  = $service->loadBlocks($record);
                            $service->createTemplate(
                                $record->company,
                                $record->name . ' (Copy)',
                                $record->template_type,
                                array_map(fn ($block) => (array) $block, $blocks)
                            );
                        })
                        ->visible(fn (ReportTemplate $record) => $record->isCloneable()),
                    DeleteAction::make('delete')
                        ->requiresConfirmation()
                        ->icon(Heroicon::OutlinedTrash)
                        ->action(function (ReportTemplate $record) {
                            app(ReportTemplateService::class)->deleteTemplate($record);
                        })
                        ->visible(fn (ReportTemplate $record) => ! $record->is_system),
                ]),
            ]);
    }
}
