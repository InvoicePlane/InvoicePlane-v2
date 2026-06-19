<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum Permission: string implements LabeledEnum
{
    // CRUD PERMISSIONS

    case VIEW_RELATIONS   = 'view-relations';
    case CREATE_RELATIONS = 'create-relations';
    case EDIT_RELATIONS   = 'edit-relations';
    case DELETE_RELATIONS = 'delete-relations';

    case IMPORT_RELATIONS    = 'import-relations';
    case EXPORT_RELATIONS    = 'export-relations';
    case DUPLICATE_RELATIONS = 'duplicate-relations';
    case MERGE_RELATIONS     = 'merge-relations';

    case VIEW_CONTACTS   = 'view-contacts';
    case CREATE_CONTACTS = 'create-contacts';
    case EDIT_CONTACTS   = 'edit-contacts';
    case DELETE_CONTACTS = 'delete-contacts';

    case VIEW_EXPENSES   = 'view-expenses';
    case CREATE_EXPENSES = 'create-expenses';
    case EDIT_EXPENSES   = 'edit-expenses';
    case DELETE_EXPENSES = 'delete-expenses';

    case VIEW_INVOICES   = 'view-invoices';
    case CREATE_INVOICES = 'create-invoices';
    case EDIT_INVOICES   = 'edit-invoices';
    case DELETE_INVOICES = 'delete-invoices';

    case VIEW_COMPANIES   = 'view-companies';
    case CREATE_COMPANIES = 'create-companies';
    case EDIT_COMPANIES   = 'edit-companies';
    case DELETE_COMPANIES = 'delete-companies';

    case VIEW_PAYMENTS   = 'view-payments';
    case CREATE_PAYMENTS = 'create-payments';
    case EDIT_PAYMENTS   = 'edit-payments';
    case DELETE_PAYMENTS = 'delete-payments';

    case VIEW_PERMISSIONS   = 'view-permissions';
    case CREATE_PERMISSIONS = 'create-permissions';
    case EDIT_PERMISSIONS   = 'edit-permissions';
    case DELETE_PERMISSIONS = 'delete-permissions';

    case VIEW_PRODUCTS   = 'view-products';
    case CREATE_PRODUCTS = 'create-products';
    case EDIT_PRODUCTS   = 'edit-products';
    case DELETE_PRODUCTS = 'delete-products';

    case VIEW_PROJECTS   = 'view-projects';
    case CREATE_PROJECTS = 'create-projects';
    case EDIT_PROJECTS   = 'edit-projects';
    case DELETE_PROJECTS = 'delete-projects';

    case VIEW_QUOTES   = 'view-quotes';
    case CREATE_QUOTES = 'create-quotes';
    case EDIT_QUOTES   = 'edit-quotes';
    case DELETE_QUOTES = 'delete-quotes';

    case VIEW_REPORTS   = 'view-reports';
    case CREATE_REPORTS = 'create-reports';
    case EDIT_REPORTS   = 'edit-reports';
    case DELETE_REPORTS = 'delete-reports';

    case VIEW_ROLES   = 'view-roles';
    case CREATE_ROLES = 'create-roles';
    case EDIT_ROLES   = 'edit-roles';
    case DELETE_ROLES = 'delete-roles';

    case VIEW_SETTINGS   = 'view-settings';
    case CREATE_SETTINGS = 'create-settings';
    case EDIT_SETTINGS   = 'edit-settings';
    case DELETE_SETTINGS = 'delete-settings';

    case VIEW_TASKS   = 'view-tasks';
    case CREATE_TASKS = 'create-tasks';
    case EDIT_TASKS   = 'edit-tasks';
    case DELETE_TASKS = 'delete-tasks';

    case VIEW_TAX_RATES   = 'view-tax-rates';
    case CREATE_TAX_RATES = 'create-tax-rates';
    case EDIT_TAX_RATES   = 'edit-tax-rates';
    case DELETE_TAX_RATES = 'delete-tax-rates';

    case VIEW_USERS   = 'view-users';
    case CREATE_USERS = 'create-users';
    case EDIT_USERS   = 'edit-users';
    case DELETE_USERS = 'delete-users';

    case VIEW_EMAIL_TEMPLATES   = 'view-email-templates';
    case CREATE_EMAIL_TEMPLATES = 'create-email-templates';
    case EDIT_EMAIL_TEMPLATES   = 'edit-email-templates';
    case DELETE_EMAIL_TEMPLATES = 'delete-email-templates';

    // Special Permissions
    case MANAGE_CUSTOMERS = 'manage-customers';

    case APPROVE_EXPENSES = 'approve-expenses';
    case REJECT_EXPENSES  = 'reject-expenses';

    case IMPORT_EXPENSES    = 'import-expenses';
    case EXPORT_EXPENSES    = 'export-expenses';
    case DUPLICATE_EXPENSES = 'duplicate-expenses';

    case DOWNLOAD_INVOICES  = 'download-invoices';
    case DUPLICATE_INVOICES = 'duplicate-invoices';
    case EMAIL_INVOICES     = 'email-invoices';
    case MARK_PAID_INVOICES = 'mark-paid-invoices';
    case MARK_SENT_INVOICES = 'mark-sent-invoices';
    case PRINT_INVOICES     = 'print-invoices';

    case IMPORT_INVOICES           = 'import-invoices';
    case EXPORT_INVOICES           = 'export-invoices';
    case CONVERT_TO_QUOTE_INVOICES = 'convert-to-quote-invoices';

    case EMAIL_PAYMENTS  = 'email-payments';
    case REFUND_PAYMENTS = 'refund-payments';

    case IMPORT_PAYMENTS = 'import-payments';
    case EXPORT_PAYMENTS = 'export-payments';

    case EXPORT_PRODUCTS    = 'export-products';
    case IMPORT_PRODUCTS    = 'import-products';
    case DUPLICATE_PRODUCTS = 'duplicate-products';

    case MANAGE_PROJECTS = 'manage-projects';

    case IMPORT_PROJECTS    = 'import-projects';
    case EXPORT_PROJECTS    = 'export-projects';
    case DUPLICATE_PROJECTS = 'duplicate-projects';
    case ARCHIVE_PROJECTS   = 'archive-projects';

    case APPROVE_QUOTES            = 'approve-quotes';
    case CONVERT_TO_INVOICE_QUOTES = 'convert-to-invoice-quotes';
    case DOWNLOAD_QUOTES           = 'download-quotes';
    case DUPLICATE_QUOTES          = 'duplicate-quotes';
    case EMAIL_QUOTES              = 'email-quotes';
    case MARK_SENT_QUOTES          = 'mark-sent-quotes';
    case PRINT_QUOTES              = 'print-quotes';
    case REJECT_QUOTES             = 'reject-quotes';

    case IMPORT_QUOTES  = 'import-quotes';
    case EXPORT_QUOTES  = 'export-quotes';
    case ARCHIVE_QUOTES = 'archive-quotes';

    case EXPORT_REPORTS = 'export-reports';
    case MANAGE_REPORTS = 'manage-reports';
    case PRINT_REPORTS  = 'print-reports';

    case MANAGE_SETTINGS = 'manage-settings';
    case MANAGE_ROLES    = 'manage-roles';

    case IMPERSONATE_USERS = 'impersonate-users';

    case VIEW_DASHBOARD          = 'view-dashboard';
    case MANAGE_COMPANY_SETTINGS = 'manage-company-settings';

    // System-wide operations
    case IMPORT  = 'import';
    case EXPORT  = 'export';
    case BACKUP  = 'backup';
    case RESTORE = 'restore';

    public function label(): string
    {
        return str($this->value)->replace('-', ' ')->title()->toString();
    }

    public function color(): string
    {
        return match(true) {
            str_starts_with($this->value, 'view')   => 'gray',
            str_starts_with($this->value, 'create') => 'success',
            str_starts_with($this->value, 'edit')   => 'warning',
            str_starts_with($this->value, 'delete') => 'danger',
            str_starts_with($this->value, 'manage') => 'primary',
            default                                 => 'secondary',
        };
    }
}
