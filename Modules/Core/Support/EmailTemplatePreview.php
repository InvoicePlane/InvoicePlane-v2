<?php

namespace Modules\Core\Support;

class EmailTemplatePreview
{
    /**
     * Replace {{ key }} placeholders in $content with values from $placeholders.
     * Unknown placeholders are left untouched.
     */
    public static function render(?string $content, array $placeholders): string
    {
        if (blank($content)) {
            return '';
        }

        return preg_replace_callback(
            '/\{\{\s*([\w.]+)\s*\}\}/',
            fn (array $match) => array_key_exists($match[1], $placeholders)
                ? (string) $placeholders[$match[1]]
                : $match[0],
            $content
        );
    }
}
