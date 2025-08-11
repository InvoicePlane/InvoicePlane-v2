<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Support\Results\Invoices;
use Modules\Core\Support\Results\Quotes;

enum DocumentGroupType: string implements LabeledEnum
{
    case CREDIT_NOTES       = 'credit_notes';
    case CUSTOMERS          = 'customers';
    case DRAFTS             = 'drafts';
    case EXPENSES           = 'expenses';
    case PRO_FORMA_INVOICES = 'pro_forma_invoices';
    case PROSPECTS          = 'prospects';
    case QUOTES             = 'quotes';
    case RECURRING_INVOICES = 'recurring_invoices';
    case INVOICES           = 'invoices';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_NOTES       => 'Credit Notes',
            self::CUSTOMERS          => 'Customers',
            self::DRAFTS             => 'Drafts',
            self::EXPENSES           => 'Expenses',
            self::PRO_FORMA_INVOICES => 'Pro Forma Invoices',
            self::PROSPECTS          => 'Prospects',
            self::QUOTES             => 'Quotes',
            self::RECURRING_INVOICES => 'Recurring Invoices',
            self::INVOICES           => 'Invoices',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREDIT_NOTES       => 'maroon',
            self::CUSTOMERS          => 'info',
            self::DRAFTS             => 'gray',
            self::EXPENSES           => 'secondary',
            self::PRO_FORMA_INVOICES => 'secondary',
            self::PROSPECTS          => 'emerald',
            self::QUOTES             => 'primary',
            self::RECURRING_INVOICES => 'success',
            self::INVOICES           => 'green',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::CREDIT_NOTES       => 'CRE',
            self::CUSTOMERS          => 'CST',
            self::DRAFTS             => 'DRA',
            self::EXPENSES           => 'EXP',
            self::PRO_FORMA_INVOICES => 'PFI',
            self::PROSPECTS          => 'PRP',
            self::QUOTES             => 'QUO',
            self::RECURRING_INVOICES => 'REC',
            self::INVOICES           => 'INV',
        };
    }
}
