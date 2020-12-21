<?php
namespace site\validate;

use think\Validate;

class ModifyPasswordValidate extends Validate
{
    protected $rule =   [
        'old_password'  => 'require',
        'new_password'  => 'require',
        'new_password2' => 'require|confirm:new_password',
    ];

    protected $message  =   [
        'old_password.require'  => '请输入当前密码',
        'new_password.require'  => '请输入新密码',
        'new_password2.require' => '请输入确认新密码',
        'new_password2.confirm' => '新密码两次输入不一致',
    ];

    protected $scene = [
        'modifypassword'  =>  ['old_password', 'new_password', 'new_password'],
    ];
}