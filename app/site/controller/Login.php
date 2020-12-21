<?php
namespace app\site\controller;

use site\controller\Identity;
use think\facade\View;

class Login extends Identity
{
    public function index()
    {
        return View::fetch();
    }
}