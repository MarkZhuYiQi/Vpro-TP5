<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/5/26
 * Time: 21:06
 */
namespace app\common\controller;
use think\Cache;

class Redis
{
    //超时时间存放key时的后缀
    const EXPIRED_KEY_SUFFIX = '_expired';

    public static function getRedisHandler()
    {
        $redis = Cache::getHandler()->handler();
        return $redis;
    }
    /**
     * 过期时间，默认传入时间是分钟
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function expiredTime($min = 0, $max = 0)
    {
        return time() + rand($min * 3600, $max * 3600);
    }

    /**
     * 判断key是否存在
     * @param $key
     * @param bool $database
     * @return bool
     */
    public static function checkRedisKey($key, $database = false)
    {
        if (!$database) {
            return self::getRedisHandler()->exists($key);
        } else {
            return self::getRedisHandler()->exists($database) && self::getRedisHandler()->hExists($database, $key);
        }
    }

    /**
     * 判断key是否过期, 过期返回false，可以使用返回true
     * @param $key
     * @param bool $database
     * @return bool
     */
    public static function checkExpired($key, $database = false)
    {
        if (!$database) {
            return self::getRedisHandler()->ttl($key . self::EXPIRED_KEY_SUFFIX) < time();
        } else {
            return self::getRedisHandler()->hGet($database . self::EXPIRED_KEY_SUFFIX, $key) < time();
        }
    }

    /**
     * hash表设置键值对带上过期功能
     * @param $database
     * @param $key
     * @param $expire_time
     * @param $value
     */
    public static function hsetex($database, $key, $expire_time, $value)
    {
        self::getRedisHandler()->hSet($database, $key, $value);
        self::getRedisHandler()->hSet($database . self::EXPIRED_KEY_SUFFIX, $key, time() + $expire_time);
    }

    /**
     * 获得带过期时间的哈希表键值对
     * @param $database
     * @param $key
     * @return bool
     */
    public static function hgetex($database, $key)
    {
        $expired_time = self::getRedisHandler()->hGet($database . self::EXPIRED_KEY_SUFFIX, $key);
        if (time() < $expired_time && self::checkRedisKey($key, $database)) {
            return self::getRedisHandler()->hGet($database, $key);
        }
        self::getRedisHandler()->hSet($database, $key, '');
        return false;
    }

    public static function scorePoint($miss)
    {
        self::getRedisHandler()->incrBy('p' . getIP(), $miss);
        self::getRedisHandler()->expire('p' . getIP(), config('key.SCORE_EXPIRE'));
    }
}