<?php
namespace common\service\interfaces;

interface UserService
{
    public function modifyPassword($userId, $oldPassword, $newPassword, $newPassword2);
}