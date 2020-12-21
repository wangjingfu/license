<?php
namespace common\service;

use common\service\interfaces\CacheService;
use common\service\interfaces\IdentityService;
use common\service\interfaces\impl\CacheServiceImpl;
use common\service\interfaces\impl\IdentityServiceImpl;
use common\service\interfaces\LicenseService;
use common\service\interfaces\LoginIpRuleService;
use common\service\interfaces\impl\LoginIpRuleServiceImpl;
use common\service\interfaces\UserService;
use site\service\LicenseServiceImpl;
use site\service\UserServiceImpl;
use think\Service;

class SiteService extends Service
{
    public function register()
    {
        $this->app->bind(LicenseService::class, LicenseServiceImpl::class);
        $this->app->bind(LoginIpRuleService::class, LoginIpRuleServiceImpl::class);
        $this->app->bind(IdentityService::class, IdentityServiceImpl::class);
        $this->app->bind(CacheService::class, CacheServiceImpl::class);
        $this->app->bind(UserService::class, UserServiceImpl::class);
    }
}