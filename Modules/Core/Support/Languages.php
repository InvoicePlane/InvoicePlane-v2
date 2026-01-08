<?php

namespace Modules\Core\Support;

class Languages
{
    /**
     * Provide a list of the available language translations.
     *
     * @return array
     */
    public static function listLanguages(): array
    {
        //Directory::listContents(base_path('resources/lang'))

        $directories = [];

        $languages = [];

        foreach ($directories as $directory) {
            $languages[$directory] = $directory;
        }

        return $languages;
    }
}
