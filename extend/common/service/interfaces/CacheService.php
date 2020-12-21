<?php
namespace common\service\interfaces;

interface CacheService
{
    const CACHE_EXPIRE_DAY = 600;

    /**
     * 获取缓存数据并可进行后续处理
     * @param string $key
     * @param mixed $closure
     * @return mixed
     */
    public function getCacheByKeyByDay($key = null, $closure = null);

    /**
     * 获取 redis 缓存句柄
     * @return object
     */
    public function getRedisCacheHandler();

    /**
     * 缓存获取键值是否过期
     * @param string $key
     * @param int $value
     * @param int $duration
     * @return bool
     */
    public function checkValueInDuration($key, $value, $duration);
}