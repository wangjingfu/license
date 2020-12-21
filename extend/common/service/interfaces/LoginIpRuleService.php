<?php
namespace common\service\interfaces;

interface LoginIpRuleService
{
    /**
     * 验证 ip 是否可以登录
     * @param $ip
     * @param int $duration
     * @return mixed
     */
    public function auth($duration = 0);

    public function clean();

    /**
     * 获取当前登录次数
     * @return mixed
     */
    public function getRecordCount();
}