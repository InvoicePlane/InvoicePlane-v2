<?php

namespace App\IpModules\MailQueue\Support;

class MailSettings
{
    /**
     * Provide a list of send methods.
     *
     * @return array
     */
    public static function listSendMethods()
    {
        return [
            ''         => '',
            'smtp'     => trans('ip.email_send_method_smtp'),
            'mail'     => trans('ip.email_send_method_phpmail'),
            'sendmail' => trans('ip.email_send_method_sendmail'),
        ];
    }

    /**
     * Provide a list of encryption methods.
     *
     * @return array
     */
    public static function listEncryptions()
    {
        return [
            '0'   => trans('ip.none'),
            'ssl' => 'SSL',
            'tls' => 'TLS',
        ];
    }
}
