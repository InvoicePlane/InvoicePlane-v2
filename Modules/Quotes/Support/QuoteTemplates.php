<?php

namespace Modules\Quotes\Support;

use Modules\Core\Support\Directory;

use Modules\Quotes\Support\QuoteTemplates;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Support\Directory;

class QuoteTemplates
{
    /**
     * Returns an array of quote templates.
     *
     * @return array
     */
    public static function lists()
    {
        $defaultTemplates = Directory::listAssocContents(app_path('IpModules/Templates/Views/templates/quotes'));

        $customTemplates = Directory::listAssocContents(base_path('custom/templates/quote_templates'));

        return $defaultTemplates + $customTemplates;
    }
}
