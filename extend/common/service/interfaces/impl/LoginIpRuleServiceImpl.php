<?php
namespace common\service\interfaces\impl;

use common\constant\Constant;
use common\service\interfaces\CacheService;
use common\service\interfaces\LoginIpRuleService;
use common\utils\LoginIpRule;
use think\Exception;
use think\facade\Log;

class LoginIpRuleServiceImpl implements LoginIpRuleService
{
    /**
     * @see LoginIpRule
     * @var LoginIpRule
     */
    private $loginIpRule;

    /**
     * @see CacheService
     * @var CacheService
     */
    private $cacheService;

    public function __construct()
    {
        $this->cacheService = app(CacheService::class);
        $redis = $this->cacheService->getRedisCacheHandler();
        $this->loginIpRule = new LoginIpRule($redis);
    }

    /**
     * @param $ip
     * @throws Exception
     */
    public function auth($duration = 0)
    {
        $ip = $this->loginIpRule->ip;
        if ($this->loginIpRule->status == LoginIpRule::STATUS_DISABLE) {
            Log::info("系统将限制IP:${ip},该IP尝试了太多次请求");
            $this->loginIpRule->setExpireTime();
            throw new Exception("用户名或密码错误，你已输入错误3次，今日无法登录", Constant::ERROR_CODE_TOO_MANY_ATTEMPTS);
        } else if (!$this->checkLastTime($duration)) {
            Log::info("系统将限制IP:${ip},时间间隔太短");
            throw new Exception('操作太频繁', Constant::ERROR_CODE_TOO_FREQUENT_OPERATION);
        } else {
            $this->loginIpRule->addRecord();
        }
        return true;
    }

    /**
     * 验证时长间隔
     * @param $duration
     * @return bool
     */
    private function checkLastTime($duration)
    {
        if (!empty($duration) && ($this->loginIpRule->record_time - $this->loginIpRule->last_login_time) < $duration) {
            return false;
        }
        return true;
    }

    /**
     * 清除ip的登录记录
     * @param $ip
     */
    public function clean()
    {
        $this->loginIpRule->clean();
    }

    /**
     * 获取当前登录次数
     * @return mixed
     */
    public function getRecordCount()
    {
        return $this->loginIpRule->record_count;
    }

}