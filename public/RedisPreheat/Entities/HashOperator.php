<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:13 AM
 */
namespace app\Entities;
class HashOperator
{
    private $redis = false;
    function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * 根据field是字符串还是数组判断是设置单个还是多个值
     * @param string $key
     * @param $field
     * @param null $value
     * @return bool|int
     */
    function set(string $key, $field, $value = null)
    {
        if (is_array($field))
        {
            return $this->redis->hMset($key, $field);
        }
        if (is_string($field) && is_string($value))
        {
            return $this->redis->hSet($key, $field, $value);
        }
        return false;
    }

    /**
     * 根据field是否为空返回redis中的hash表值
     * @param string $key
     * @param string|null $field
     */
    function get(string $key, string $field=null)
    {
        if ($field === null)
        {
            var_export($key);
            return $this->redis->hGetAll($key);
        }
        else
        {
            return $this->redis->hGet($key, $field);
        }
    }

    /**
     * 单个值的累加
     * @param string $key
     * @param string $field
     * @param int $value
     * @return int
     */
    function incr(string $key, string $field, int $value=1)
    {
        return $this->redis->hIncrBy($key, $field, $value);
    }
}