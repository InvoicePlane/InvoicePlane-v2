<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidFile implements Rule
{
    public function passes($attribute, $value)
    {
        return is_file($value);
    }

    public function message()
    {
        return trans('ip.pdf_driver_wkhtmltopdf');
    }
}
