<?php
namespace common\service\interfaces;

interface IdentityService
{
    /**
     * 登录
     * @param String $username
     * @param String $password
     * @return mixed
     */
    public function login($username, $password);

    /**
     * 登出
     * @return boolean
     */
    public function logout();

    /**
     * 获取当前登录用户
     * @return array
     */
    public function getLoginUserInfo();
}