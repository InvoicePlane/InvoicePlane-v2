<?php

namespace Modules\Core\Enums;

enum ReportDataSource: string
{
    case COMPANY      = 'company';
    case CUSTOMER     = 'customer';
    case EXPENSE      = 'expense';
    case INVOICE      = 'invoice';
    case INVOICE_ITEM = 'invoice_item';
    case PAYMENT      = 'payment';
    case PROJECT      = 'project';
    case QUOTE        = 'quote';
    case QUOTE_ITEM   = 'quote_item';
    case RELATION     = 'relation';
    case TASK         = 'task';
}
