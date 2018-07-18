<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 6/29/2018
 * Time: 10:40 AM
 */
namespace app\api;
use app\common\controller\Redis;
class BaseApi
{
    protected $redis;
    function __construct()
    {
        $this->redis = Redis::getRedisHandler();
    }
}