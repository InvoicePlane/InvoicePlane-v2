<?php

namespace Modules\Core\Filament\Admin\Resources;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\ListEmailTemplates;

use Modules\Core\Models\EmailTemplate;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

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
                                TextInput::make('title')
                                    ->label(trans('ip.title'))
                                    ->required()
                                    ->autofocus(),
                                TextInput::make('from_name')
                                    ->label(trans('ip.from_name')),
                                TextInput::make('from_email')
                                    ->label(trans('ip.from_email')),
                            ])->columns(1),
                        Section::make(heading:trans('ip.cc_and_bcc'))
                            ->collapsed()
                            ->schema([
                                TextInput::make('cc')->label(trans('ip.cc')),
                                TextInput::make('bcc')->label(trans('ip.bcc')),
                            ])->columns(1),
                    ]),
                Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                                TextInput::make('type')->label(trans('ip.type')),
                                TextInput::make('subject')->label(trans('ip.subject')),
                            ])->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->limit(10)->label(trans('ip.title'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('type')->label(trans('ip.type'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('subject')->limit(10)->label(trans('ip.subject'))->hiddenFrom('sm')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('from_name')->limit(10)->label(trans('ip.from_name'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('from_email')->limit(10)->label(trans('ip.from_email'))->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title', 'asc');
    }

    /**
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => EmailTemplateResource\Pages\ListEmailTemplates::route('/'),
        ];
    }
}
