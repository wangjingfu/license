<?php
// 应用公共文件

if (!function_exists('currentDateBeginTime')) {
    /**
     * 获取指定日期当天开始时间点
     * @param string $date
     * @return int
     */
    function currentDateBeginTime($date)
    {
        $time = strtotime($date);
        return mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    }
}

if (!function_exists('currentDateEndTime')) {
    /**
     * 获取指定日期当天结束时间点
     * @param string $date
     * @return int
     */
    function currentDateEndTime($date)
    {
        $time = strtotime($date);
        return mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));
    }
}

if (!function_exists('createDirectory')) {
    function createDirectory($path)
    {
        if (!is_dir($path)) {
            if (false === mkdir($path, 0775, true)) {
                return false;
            }
        }
        return true;
    }
}