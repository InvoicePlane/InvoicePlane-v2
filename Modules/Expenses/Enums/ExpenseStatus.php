<?php

namespace Modules\Expenses\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum ExpenseStatus: string implements LabeledEnum
{
    use HasOptions;
    case DRAFT      = 'draft';
    case SUBMITTED  = 'submitted';
    case APPROVED   = 'approved';
    case REIMBURSED = 'reimbursed';
    case BILLED     = 'billed';
    case PAID       = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT      => trans('enums.expense_status.draft'),
            self::SUBMITTED  => trans('enums.expense_status.submitted'),
            self::APPROVED   => trans('enums.expense_status.approved'),
            self::REIMBURSED => trans('enums.expense_status.reimbursed'),
            self::BILLED     => trans('enums.expense_status.billed'),
            self::PAID       => trans('enums.expense_status.paid'),
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
