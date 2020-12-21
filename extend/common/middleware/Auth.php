<?php
namespace common\middleware;

use common\constant\Constant;
use think\facade\Session;

class Auth
{
    public function handle($request, \Closure $next)
    {
        $user = Session::get(Constant::LOGIN_SESSION_SAVE_NAME, null);
        if (empty($user)) {
            if ($request->isAjax()) {
                $response = [];
                $response['status'] = Constant::ERROR_CODE_NOT_LOGIN;
                $response['info'] = '当前未登录，请重新登录';
                $response['data'] = [];
                return json($response);
            } else {
                return redirect((string) url('site/login/index'));
            }
        }

        return $next($request);
    }
}