<?php

namespace Modules\Core\Support;

class Languages
{
    /**
     * Provide a list of the available language translations.
     *
     * @return array
     */
    public static function listLanguages()
    {
        $directories = Directory::listContents(base_path('resources/lang'));

        $languages = [];

        foreach ($directories as $directory) {
            $languages[$directory] = $directory;
        }

        return $languages;
    }
}
