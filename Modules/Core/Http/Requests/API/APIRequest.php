<?php

namespace Modules\Core\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class APIRequest extends FormRequest
{
    public function response(array $errors): JsonResponse
    {
        $messages = implode(' ', Arr::flatten($errors));

        return response()->json([]);
    }
}
