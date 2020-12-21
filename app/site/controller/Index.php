<?php
namespace app\site\controller;

use site\controller\License;
use think\facade\View;

class Index extends License
{
    public function index()
    {
        return View::fetch();
    }
}