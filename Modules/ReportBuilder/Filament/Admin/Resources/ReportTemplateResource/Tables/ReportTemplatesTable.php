<?php

namespace Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\ReportBuilder\Models\ReportTemplate;
use Modules\ReportBuilder\Services\ReportTemplateService;

class ReportTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('template_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('template_type')
                    ->label('Template Type')
                    ->options([
                        'invoice' => 'Invoice',
                        'quote' => 'Quote',
                        'estimate' => 'Estimate',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
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
                        ->visible(fn (ReportTemplate $record) => !$record->is_system),
                    Action::make('design')
                        ->label('Design')
                        ->icon(Heroicon::OutlinedPaintBrush)
                        ->url(fn (ReportTemplate $record) => route('filament.admin.resources.report-templates.design', ['record' => $record->id]))
                        ->visible(fn (ReportTemplate $record) => !$record->is_system),
                    Action::make('clone')
                        ->label('Clone')
                        ->icon(Heroicon::OutlinedDocumentDuplicate)
                        ->requiresConfirmation()
                        ->action(function (ReportTemplate $record) {
                            $service = app(ReportTemplateService::class);
                            $blocks = $service->loadBlocks($record);
                            $service->createTemplate(
                                $record->company,
                                $record->name . ' (Copy)',
                                $record->template_type,
                                array_map(fn ($block) => (array) $block, $blocks)
                            );
                        })
                        ->visible(fn (ReportTemplate $record) => $record->isCloneable()),
                    DeleteAction::make('delete')
                        ->icon(Heroicon::OutlinedTrash)
                        ->action(function (ReportTemplate $record) {
                            app(ReportTemplateService::class)->deleteTemplate($record);
                        })
                        ->visible(fn (ReportTemplate $record) => !$record->is_system),
                ]),
            ]);
    }
}
