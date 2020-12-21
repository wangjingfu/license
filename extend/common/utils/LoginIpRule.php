<?php
namespace common\utils;

use think\facade\Config;
use think\facade\Log;
use think\facade\Request;

class LoginIpRule
{
    /**
     * 禁止时间 自然天
     */
    const FORBIDDEN_DAYS = 1;

    const ALLOW_DURATION_TIMES = 3;

    // 检查时间段 秒
    const CHECK_DURATION_TIME = 600;

    // ip状态 不可用
    const STATUS_DISABLE = 1;

    // ip状态 可用
    const STATUS_ENABLE = 0;

    /**
     * @see \Redis
     * @var \Redis
     */
    private $redis = null;

    // 最后一次登录
    public $last_login_time = 0;

    /**
     * 当天最后一条记录
     */
    public $last_record;

    // 当前监测时段记录
    public $namespace = "ipStore";

    public $status = 0;

    public $ip = "";

    public $storeKey;

    public $record_count;

    /**
     * 请求发起时的时间
     * @var
     */
    public $record_time;

    public function __construct($redis)
    {
        $this->ip = Request::instance()->ip();
        Log::info("当前尝试登录ip为: {$this->ip}");
        $this->storeKey = "{$this->namespace}:ip{$this->ip}";
        $this->redis = $redis;
        $this->record_time = time();
        $this->_init();
    }

    /**
     * 初始化操作
     * 验证当前 ip 的登录请求情况
     */
    private function _init()
    {
        $this->clean_invalid_record();
        $this->record_count = $this->redis->zCard($this->storeKey);
        Log::info("{$this->storeKey}有效记录数量为： {$this->record_count}");
        $this->last_record = $this->redis->zRevRange($this->storeKey, 0, 0, true);
        if (empty($this->last_record) || empty($this->record_count)) {
            $this->status = self::STATUS_ENABLE;
        } else {
            $recordName = array_keys($this->last_record)[0];
            Log::info("最新一条记录的名称: {$recordName}");
            $this->last_login_time = $this->last_record[$recordName];
            Log::info("上次登录时间为: ". date("Y-m-d H:i:s", $this->last_login_time));
            if (strpos($recordName, "_s1") !== false) {
                $this->status = self::STATUS_DISABLE;
            } else {
                $this->status = self::STATUS_ENABLE;
            }
        }
        Log::info("当前状态为: {$this->status}");
    }

    /**
     * 清除失效的数据
     * @param $time
     */
    private function clean_invalid_record()
    {
        $check_duration_time = Config::has("app.check_duration_time") ? (int) Config::get("app.check_duration_time") : self::CHECK_DURATION_TIME;
        Log::info("防止刷新时间间隔: {$check_duration_time}秒");
        $invalid_time = $this->record_time - $check_duration_time;
        Log::info("删除截止时间为: " . date("Y-m-d H:i:s", $invalid_time) ."的数据");
        $this->redis->zRemRangeByScore($this->storeKey, 0, $invalid_time);
    }

    /**
     * @return bool
     */
    public function addRecord()
    {
        $try_count = Config::has("app.allow_try_count") ? (int) Config::get("app.allow_try_count") : self::ALLOW_DURATION_TIMES;
        Log::info("允许尝试登录次数: {$try_count}");
        if ($this->record_count == ($try_count - 1)) {
            $memberName = $this->buildMemberName()."_s1";
        } else {
            $memberName = $this->buildMemberName()."_s0";
        }
        $this->redis->zAdd($this->storeKey, $this->record_time, $memberName, 0);
        $this->record_count = $this->redis->zCard($this->storeKey);
        Log::info("保存到Redis数据{". $this->record_time ."}key = {$this->storeKey}");
        //首次需要设置过期时间
        if (empty($this->record_count)) {
            $this->setExpireTime();
        }
        return true;
    }

    /**
     * 登录成功
     * 需要清理尝试记录
     * @param  $ip
     */
    public function clean()
    {
        $this->redis->del($this->storeKey);
        Log::info("正在开放IP: {$this->ip}的限制");
    }

    /**
     * 设置缓存过期时间
     * 初始化设置一次 登录超限设置一次
     */
    public function setExpireTime()
    {
        $forbidden_days = Config::has("app.forbidden_days") ? (int) Config::get("app.forbidden_days") : self::FORBIDDEN_DAYS;
        Log::info("禁止登录天数: {$forbidden_days}");
        $forbidden_days = $forbidden_days - 1;
        $end_day_timestamp = mktime(23, 59, 59, date("m"), date("d") + $forbidden_days, date("Y"));
        $expire_time = $end_day_timestamp - $this->record_time;
        $this->redis->expire($this->storeKey, $expire_time);
        Log::info("设置截止日期" . date("Y-m-d H:i:s", $end_day_timestamp));
    }

    /**
     * 创建存储键名称
     * @return bool|string
     */
    private function buildMemberName()
    {
        $returnValue = session_create_id();
        Log::info("新的变量名称 $returnValue");
        return $returnValue;
    }
}