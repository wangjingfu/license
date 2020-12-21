<?php
namespace common\service\interfaces\impl;

use common\constant\Constant;
use common\dao\UserDao;
use common\service\interfaces\IdentityService;
use think\Exception;
use think\facade\Config;
use think\facade\Log;
use think\facade\Request;
use think\facade\Session;

class IdentityServiceImpl implements IdentityService
{
    protected $userDao;

    public function __construct()
    {
        $this->userDao = new UserDao();
    }

    public function login($username, $password)
    {
        Log::info("开始校验登录账户信息");
        $this->logout();
        $user = $this->userDao->getInfoByWhere([
            'username' => $username,
        ]);
        if ($user) {
            if ($user['status'] == Constant::STATUS_USER_ENABLE) {
                $salt = Config::get('app.passwd_salt');
                if ($user['passwd'] === hash('md5', $password . $salt)) {
                    $saveUserInfo = [];
                    $saveUserInfo['id'] = $user['id'];
                    $saveUserInfo['username'] = $user['username'];
                    $saveUserInfo['nickname'] = $user['nickname'];
                    $saveUserInfo['realname'] = $user['id'];
                    $saveUserInfo['last_login'] = $user['last_login'];
                    $saveUserInfo['last_ip'] = $user['last_ip'];
                    Session::set(Constant::LOGIN_SESSION_SAVE_NAME, $saveUserInfo);

                    $this->userDao->updateByWhere([
                        'last_ip' => Request::instance()->ip(),
                        'last_login' => time(),
                        'update_time' => time(),
                    ], [
                        'id' => $user['id'],
                    ]);
                    return true;
                } else {
                    throw new Exception('用户名或密码错误', Constant::ERROR_CODE_USERNAME_OR_PASSWORD_ERROR);
                }
            } else {
                throw new Exception('当前用户已被禁用', Constant::ERROR_CODE_USER_IS_FORBIDDEN);
            }
        } else {
            throw new Exception('用户名或密码错误', Constant::ERROR_CODE_USERNAME_OR_PASSWORD_ERROR);
        }
    }

    public function logout()
    {
        Session::delete(Constant::LOGIN_SESSION_SAVE_NAME);
        return true;
    }

    public function getLoginUserInfo()
    {
        return Session::get(Constant::LOGIN_SESSION_SAVE_NAME, []);
    }
}