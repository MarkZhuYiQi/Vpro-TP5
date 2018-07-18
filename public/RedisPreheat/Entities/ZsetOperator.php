<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/11/2018
 * Time: 9:48 AM
 */
namespace app\Entities;
class ZsetOperator
{
    private $redis = false;
    public $setName = 'courses_click';
    function __construct($redis)
    {
        $this->redis = $redis;
    }
    function set(int $score, string $member)
    {
        return $this->redis->zAdd($this->setName, $score, $member);
    }
    function get(string $member):int
    {
        $getScore = $this->redis->zScore($this->setName, $member);
        if (!$getScore)
        {
            $getScore = 0;
        }
        return $getScore;
    }
    function incr(string $member)
    {
        $this->redis->zIncrBy($this->setName, 1, $member);
    }
}