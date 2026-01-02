<?php

namespace Modules\Core\Enums;

enum ReportBand: string
{
    case DETAILS = 'details';
    case FOOTER = 'footer';
    case GROUP_FOOTER = 'group_footer';
    case GROUP_HEADER = 'group_header';
    case HEADER = 'header';
}
