<?php
namespace site\service;

use common\constant\Constant;
use common\dao\UserDao;
use common\service\interfaces\UserService;
use think\Exception;
use think\facade\Config;

class UserServiceImpl implements UserService
{
    /**
     * @var UserDao
     * @see UserDao
     */
    protected $userDao;

    public function __construct()
    {
        $this->userDao = new UserDao();
    }

    public function modifyPassword($userId, $oldPassword, $newPassword, $newPassword2)
    {
        $userInfo = $this->userDao->getInfoById($userId);
        if ($userInfo) {
            $salt = Config::get('app.passwd_salt');
            if ($userInfo['passwd'] === hash('md5', $oldPassword . $salt)) {
                $this->userDao->updateByWhere([
                    'passwd' => $newPassword,
                    'update_time' => time(),
                ], [
                    'id' => $userId,
                ]);
                return true;
            } else {
                throw new Exception('当前密码不正确', Constant::ERROR_CODE_REQUEST_ERROR);
            }
        } else {
            throw new Exception('当前用户不存在', Constant::ERROR_CODE_REQUEST_ERROR);
        }
    }
}