<?php

namespace Modules\Core\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Modules\Core\Utils\ResponseUtil;

class APIRequest extends FormRequest
{
    public function response(array $errors): JsonResponse
    {
        $messages = implode(' ', Arr::flatten($errors));

        return response()->json(ResponseUtil::makeError($messages), Response::HTTP_BAD_REQUEST);
    }
}
