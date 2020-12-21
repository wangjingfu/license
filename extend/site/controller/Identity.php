<?php
namespace site\controller;

use common\constant\Constant;
use common\controller\Controller;
use common\service\interfaces\IdentityService;
use common\service\interfaces\LoginIpRuleService;
use think\App;
use think\facade\Config;
use think\facade\Log;

class Identity extends Controller
{
    /**
     * @var LoginIpRuleService
     * @see LoginIpRuleService
     */
    protected $loginIpRuleService;

    /**
     * @var IdentityService
     * @see IdentityService
     */
    protected $identityService;

    public function __construct(App $app, IdentityService $identityService, LoginIpRuleService $loginIpRuleService)
    {
        parent::__construct($app);
        $this->identityService = $identityService;
        $this->loginIpRuleService = $loginIpRuleService;
    }

    public function login()
    {
        $username = $this->request->param("username");
        $password = $this->request->param("password");
        $captcha = $this->request->param("captcha");

        $allow_duration_time = Config::get("allow_duration_time");
        try {
            if (captcha_check($captcha)) {
                if ($this->loginIpRuleService->auth($allow_duration_time)) {
                    $this->identityService->login(trim($username), trim($password));
                    $this->loginIpRuleService->clean();
                    return $this->result(true);
                } else {
                    return $this->result(false);
                }
            } else {
                return $this->result([], Constant::ERROR_CODE_CAPTCHA_CHECK_ERROR, '验证码不正确');
            }
        } catch (\Exception $exception) {
            Log::info($exception->getTraceAsString());
            return $this->result([], $exception->getCode(), $exception->getMessage());
        }
    }

    public function logout()
    {
        $this->identityService->logout();
        return $this->result(true);
    }
}