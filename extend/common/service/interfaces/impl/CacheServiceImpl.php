<?php
namespace common\service\interfaces\impl;

use common\service\interfaces\CacheService;
use think\facade\Cache;

class CacheServiceImpl implements CacheService
{
    /**
     * 获取缓存数据并可进行后续处理
     * @param string $key
     * @param mixed $closure
     * @return mixed
     */
    public function getCacheByKeyByDay($key = null, $closure = null)
    {
        $data = Cache::get($key);
        if (empty($data)) {
            if ($closure instanceof \Closure) {
                $data = $closure();
                $data && Cache::set($key, $data, self::CACHE_EXPIRE_DAY);
            }
        }
        return $data;
    }

    /**
     * 获取 redis 缓存句柄
     * @see \Redis
     * @return object \Redis
     */
    public function getRedisCacheHandler()
    {
        return Cache::store()->handler();
    }

    /**
     * 缓存获取键值是否过期
     * @param string $key
     * @param int $value
     * @param int $duration
     * @return bool
     */
    public function checkValueInDuration($key, $value, $duration)
    {
        if ($duration === 0) return false;
        $data = Cache::get($key);
        if (empty($data)) {
            Cache::set($key, $value);
            return false;
        } else {
            if (($value - $data) < $duration) {
                return true;
            } else {
                Cache::delete($key);
                return false;
            }
        }
    }

    public function __call($method, $params)
    {
        $redis = $this->getRedisCacheHandler();
        return call_user_func_array([$redis, $method], $params);
    }
}