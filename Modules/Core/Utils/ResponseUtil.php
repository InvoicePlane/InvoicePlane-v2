<?php

namespace Modules\Core\Utils;

use Modules\Core\Utils\ResponseUtil;

class ResponseUtil
{
    public static function makeResponse(string $message, mixed $data): array
    {
        return [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];
    }

    public static function makeError(string $message, array $data = []): array
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        if ( ! empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }
}
