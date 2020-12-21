<?php
namespace common\exception\handle;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;

class CommonHandle extends Handle
{
    public function render($request, \Throwable $e): Response
    {
        Log::error("系统异常：{$e->getMessage()}");
        Log::error($e->getTraceAsString());
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && $request->isAjax()) {
            $response = [];
            $response['status'] = $e->getStatusCode();
            $response['info'] = $e->getMessage();
            $response['data'] = [];
            return json($response);
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}