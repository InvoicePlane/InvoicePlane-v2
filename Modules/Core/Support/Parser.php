<?php

namespace Modules\Core\Support;

use Exception;

class Parser
{
    public function __construct($object)
    {
        $this->object = $object;

        $this->class = class_basename(get_class($object));
    }

    public function parse($template)
    {
        try {
            return view('app.email_templates.' . $template)
                ->with(mb_strtolower($this->class), $this->object)
                ->render();
        } catch (Exception $e) {
            abort(500);
        }
    }
}
