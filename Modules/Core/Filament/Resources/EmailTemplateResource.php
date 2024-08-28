<?php

namespace Modules\Core\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Filament\Resources\EmailTemplateResource\Pages;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return trans('crud.email_templates.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.email_templates.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.email_templates.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email_template_title')
                    ->required()
                    ->autofocus(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email_template_type'),
                TextColumn::make('email_template_title'),
                TextColumn::make('email_template_subject')->hiddenFrom('sm'),
                TextColumn::make('email_template_from_name'),
                TextColumn::make('email_template_from_email'),
                TextColumn::make('email_template_pdf_template'),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('email_template_title', 'asc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmailTemplates::route('/'),
        ];
    }
}
