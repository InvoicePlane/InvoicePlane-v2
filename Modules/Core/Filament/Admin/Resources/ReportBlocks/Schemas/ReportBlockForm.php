<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\ReportBlockWidth;

class ReportBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                ]),
        ]);
    }

    protected static function getAvailableFields(): array
    {
        return [
            'company_name'      => 'Company Name',
            'company_address'   => 'Company Address',
            'company_phone'     => 'Company Phone',
            'company_email'     => 'Company Email',
            'company_vat_id'    => 'Company VAT ID',
            'client_name'       => 'Client Name',
            'client_address'    => 'Client Address',
            'client_phone'      => 'Client Phone',
            'client_email'      => 'Client Email',
            'invoice_number'    => 'Invoice Number',
            'invoice_date'      => 'Invoice Date',
            'invoice_due_date'  => 'Due Date',
            'invoice_subtotal'  => 'Subtotal',
            'invoice_tax_total' => 'Tax Total',
            'invoice_total'     => 'Invoice Total',
            'item_description'  => 'Item Description',
            'item_quantity'     => 'Item Quantity',
            'item_price'        => 'Item Price',
            'item_tax_name'     => 'Item Tax Name',
            'item_tax_rate'     => 'Item Tax Rate',
            'footer_notes'      => 'Notes',
        ];
    }
}
