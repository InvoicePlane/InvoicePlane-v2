<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Services\EmailTemplateVariableResolver;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedEnvelopeOpen)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(trans('ip.title'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->autofocus()
                                            ->columnSpan(4),

                                        Select::make('type')
                                            ->label(trans('ip.type'))
                                            ->prefixIcon(Heroicon::OutlinedListBullet)
                                            ->required()
                                            ->options(EmailTemplateType::class)
                                            ->default(null)
                                            ->columnSpan(2),

                                        TextInput::make('subject')
                                            ->label(trans('ip.subject'))
                                            ->prefixIcon(Heroicon::OutlinedChatBubbleLeftRight)
                                            ->columnSpan(2),

                                        TextInput::make('from_name')
                                            ->label(trans('ip.from_name'))
                                            ->prefixIcon(Heroicon::OutlinedUser)
                                            ->columnSpan(2),

                                        TextInput::make('from_email')
                                            ->label(trans('ip.from_email'))
                                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.cc_and_bcc'))
                            ->icon(Heroicon::OutlinedAtSymbol)
                            ->columnSpan(1)
                            ->collapsed()
                            ->schema([
                                TextInput::make('cc')
                                    ->label(trans('ip.cc'))
                                    ->prefixIcon(Heroicon::OutlinedAtSymbol),
                                TextInput::make('bcc')
                                    ->label(trans('ip.bcc'))
                                    ->prefixIcon(Heroicon::OutlinedAtSymbol),
                            ]),
                    ]),

                Section::make(trans('ip.body'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('body')
                            ->label(trans('ip.body'))
                            ->rows(10)
                            // email_templates.body is a NOT NULL
                            // longText column with no default —
                            // without this, a blank body passes
                            // client validation and blows up as an
                            // unhandled SQL 500. Shared by both the
                            // admin and company panel resources.
                            ->required(),
                    ]),

                Section::make(trans('ip.available_variables'))
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Text::make(fn (): HtmlString => new HtmlString(
                            collect(app(EmailTemplateVariableResolver::class)->variables())
                                ->map(fn (string $description, string $tag): string => '<div><code>' . e($tag) . '</code> — ' . e($description) . '</div>')
                                ->implode('')
                        )),
                    ]),
            ]);
    }
}
