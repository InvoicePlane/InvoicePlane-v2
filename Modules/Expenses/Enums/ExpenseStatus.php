<?php

namespace Modules\Expenses\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum ExpenseStatus: string implements LabeledEnum
{
    case DRAFT      = 'draft';
    case SUBMITTED  = 'submitted';
    case APPROVED   = 'approved';
    case REIMBURSED = 'reimbursed';
    case BILLED     = 'billed';
    case PAID       = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT      => 'Draft',
            self::SUBMITTED  => 'Submitted',
            self::APPROVED   => 'Approved',
            self::REIMBURSED => 'Reimbursed',
            self::BILLED     => 'Billed',
            self::PAID       => 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT      => 'gray',
            self::SUBMITTED  => 'blue',
            self::APPROVED   => 'emerald',
            self::REIMBURSED => 'green',
            self::BILLED     => 'indigo',
            self::PAID       => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT      => 'heroicon-o-document-text',
            self::SUBMITTED  => 'heroicon-o-document-text',
            self::APPROVED   => 'heroicon-o-document-text',
            self::REIMBURSED => 'heroicon-o-document-text',
            self::BILLED     => 'heroicon-o-document-text',
            self::PAID       => 'heroicon-o-document-text',
        };
    }
}
