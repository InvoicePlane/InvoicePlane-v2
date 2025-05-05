<?php

namespace App\IpModules\Invoices\Support;

use App\Support\Directory;

class InvoiceTemplates
{
    /**
     * Returns an array of invoice templates.
     *
     * @return array
     */
    public static function lists()
    {
        $defaultTemplates = Directory::listAssocContents(app_path('IpModules/Templates/Views/templates/invoices'));

        $customTemplates = Directory::listAssocContents(base_path('custom/templates/invoice_templates'));

        return $defaultTemplates + $customTemplates;
    }
}
