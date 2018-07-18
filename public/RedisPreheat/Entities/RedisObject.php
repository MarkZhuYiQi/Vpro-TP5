<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:03 AM
 */
namespace app\Entities;

require 'HashOperator.php';
require 'ZsetOperator.php';

class RedisObject{
    protected $redis;
    public $stringOperator;
    public $hashOperator;
    public $listOperator;
    public $zsetOperator;
    function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('192.138.0.5', 6379);
        $this->redis->auth('7777777y');
        $this->hashOperator = new HashOperator($this->redis);
        $this->zsetOperator = new ZsetOperator($this->redis);
    }
    function client()
    {
        return $this->redis;
    }
    function pipeExec(callable $callback)
    {
        $this->redis->multi(\Redis::PIPELINE);
        $callback($this->redis);
    }
    function mutiExec(callable $callback)
    {
        $this->redis->multi(\Redis::MULTI);
        $callback($this->redis);
        $this->redis->exec();
    }
    function subscribe(string $channel, string $callback)
    {
        $subClient = new \Redis();
        $subClient->connect('192.138.0.5', 6379);
        $subClient->auth('7777777y');
        $subClient->subscribe([$channel], $callback);
    }
}