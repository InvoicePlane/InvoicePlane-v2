<?php

namespace Modules\Core\Filament\Resources;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
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

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return trans('ip.email_templates');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.email_templates');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.email_templates');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema([
                                TextInput::make('email_template_title')
                                    ->label(trans('ip.title'))
                                    ->required()
                                    ->autofocus(),
                                TextInput::make('email_template_from_name')
                                    ->label(trans('ip.from_name')),
                                TextInput::make('email_template_from_email')
                                    ->label(trans('ip.from_email')),
                            ])->columns(1),
                        Section::make(heading:trans('ip.cc_and_bcc'))
                            ->collapsed()
                            ->schema([
                                TextInput::make('email_template_cc')->label(trans('ip.cc')),
                                TextInput::make('email_template_bcc')->label(trans('ip.bcc')),
                            ])->columns(1),
                    ]),
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                                TextInput::make('email_template_type')->label(trans('ip.type')),
                                TextInput::make('email_template_subject')->label(trans('ip.subject')),
                            ])->columns(1),
                    ]),
            ]);
    }

    public static function oldForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email_template_title')
                    ->required()
                    ->autofocus(),
                TextInput::make('email_template_type')->label(trans('ip.type')),
                TextInput::make('email_template_from_name')->label(trans('ip.from_name')),
                TextInput::make('email_template_from_email')->label(trans('ip.from_email')),
                TextInput::make('email_template_cc')->label(trans('ip.cc')),
                TextInput::make('email_template_bcc')->label(trans('ip.bcc')),
                TextInput::make('email_template_subject')->label(trans('ip.subject')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email_template_title')->label(trans('ip.title')),
                TextColumn::make('email_template_type')->label(trans('ip.type')),
                TextColumn::make('email_template_subject')->label(trans('ip.subject'))->hiddenFrom('sm'),
                TextColumn::make('email_template_from_name')->label(trans('ip.from_name')),
                TextColumn::make('email_template_from_email')->label(trans('ip.from_email')),
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
