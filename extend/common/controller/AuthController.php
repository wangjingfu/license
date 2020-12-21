<?php
namespace common\controller;

use common\middleware\Auth;
use common\service\interfaces\IdentityService;
use think\App;
use think\facade\View;

class AuthController extends Controller
{
    protected $currentUser = [];

    protected $middleware = [
        Auth::class,
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->currentUser = \app(IdentityService::class)->getLoginUserInfo();
        View::assign('currentUser', $this->currentUser);
    }
}