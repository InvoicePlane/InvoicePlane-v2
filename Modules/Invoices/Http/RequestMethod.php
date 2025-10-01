<?php

namespace Modules\Invoices\Http;

/**
 * RequestMethod - Enum for HTTP request methods.
 *
 * Provides type-safe HTTP method constants for API requests.
 *
 * @package Modules\Invoices\Http
 */
enum RequestMethod: string
{
    case GET = 'get';
    case POST = 'post';
    case PUT = 'put';
    case PATCH = 'patch';
    case DELETE = 'delete';
    case HEAD = 'head';
    case OPTIONS = 'options';
}
