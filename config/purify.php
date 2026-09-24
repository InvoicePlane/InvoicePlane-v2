<?php

use Stevebauman\Purify\Definitions\Html5Definition;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Config
    |--------------------------------------------------------------------------
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Config sets
    |--------------------------------------------------------------------------
    |
    | `default` mirrors the package default. `report` is the locked-down set
    | used for the report-builder rich-text bricks (footer notes / terms /
    | summary): body-copy formatting only — no <img>, no <a>, no style
    | attributes, and no external resource fetching. This closes the blind
    | SSRF that a headless-Chromium (Browsershot) render would otherwise allow
    | via <img src="http://internal/...">.
    |
    */

    'configs' => [
        'default' => [
            'Core.Encoding'            => 'utf-8',
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'h1,h2,h3,h4,h5,h6,b,u,strong,i,em,s,del,a[href|title],ul,ol,li,p[style],br,span,img[width|height|alt|src],blockquote',
            'HTML.ForbiddenElements'   => '',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
        ],

        'report' => [
            'Core.Encoding'                => 'utf-8',
            'HTML.Doctype'                 => 'HTML 4.01 Transitional',
            'HTML.Allowed'                 => 'h1,h2,h3,h4,h5,h6,b,u,strong,i,em,s,del,ul,ol,li,p,br,span,blockquote',
            'HTML.ForbiddenElements'       => 'script,style,iframe,object,embed,form,input,link,base',
            'CSS.AllowedProperties'        => '',
            'URI.AllowedSchemes'           => ['https' => true, 'mailto' => true],
            'URI.DisableExternalResources' => true,
            'URI.DisableResources'         => true,
            'AutoFormat.AutoParagraph'     => false,
            'AutoFormat.RemoveEmpty'       => true,
        ],
    ],

    'definitions' => Html5Definition::class,

    'css-definitions' => null,

    'serializer' => [
        'driver' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
        'cache'  => \Stevebauman\Purify\Cache\CacheDefinitionCache::class,
    ],
];
