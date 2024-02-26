<?php

declare(strict_types=1);

namespace yangweijie\jwt\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

trait ResponseJson
{
    /**
     * 返回成功 json
     */
    protected function success(
        string $message,
        mixed $data = [],
        array $headers = [],
        int $code = 0
    ) {
        $data = [
            'data' => $data,
            'msg' => $message,
            'code' => $code,
        ];
        Log::info($message);

        return Request::expectsJson() ?
            Response::json($data, $code, $headers) :
            redirect('home')->with('message', $message);
    }

    /**
     * 返回错误 json
     */
    protected function error(
        string $message,
        int $code = 1,
        mixed $data = [],
        array $headers = []
    ) {
        $data = [
            'data' => $data,
            'msg' => $message,
            'code' => $code,
        ];

        Log::error($message);

        return Request::expectsJson() ?
            Response::json($data, $code, $headers) :
            redirect('login')->with('message', $message);
    }

    /**
     * 将数组以标准 json 格式返回
     */
    protected function returnJson(array $data, $header = [])
    {
        return Response::returnJson($data, $header);
    }

    /**
     * 将 json 字符窜以标准 json 格式返回
     */
    protected function returnJsonFromString($contents, $header = [])
    {
        return Response::returnJsonFromString($contents, $header);
    }

    /**
     * 返回字符
     */
    protected function returnString($contents, $header = [])
    {
        return Response::returnString($contents, $header);
    }
}
