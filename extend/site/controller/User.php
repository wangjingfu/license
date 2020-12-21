<?php
namespace site\controller;

use common\constant\Constant;
use common\controller\AuthController;
use common\service\interfaces\UserService;
use site\validate\ModifyPasswordValidate;
use think\App;
use think\Exception;
use think\exception\ValidateException;

class User extends AuthController
{
    /**
     * @var UserService
     * @see UserService
     */
    protected $userService;

    public function __construct(App $app, UserService $userService)
    {
        parent::__construct($app);
        $this->userService = $userService;
    }

    public function modifyPassword()
    {
        try {
            $result = $this->validate($this->request->param(), ModifyPasswordValidate::class);
            if (true !== $result) {
                return $this->result([], Constant::ERROR_CODE_REQUEST_ERROR, $result);
            }
            $oldPassword = $this->request->param('old_password');
            $newPassword = $this->request->param('new_password');
            $newPassword2 = $this->request->param('new_password2');
            $result = $this->userService->modifyPassword($this->currentUser['id'], $oldPassword, $newPassword, $newPassword2);
            return $this->result($result);
        } catch (ValidateException $exception) {
            return $this->result([], Constant::ERROR_CODE_REQUEST_ERROR, $exception->getMessage());
        } catch (Exception $exception) {
            return $this->result([], $exception->getCode(), $exception->getMessage());
        }
    }
}