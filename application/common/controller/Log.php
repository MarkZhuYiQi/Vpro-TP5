<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 5/30/2018
 * Time: 2:38 PM
 */

namespace app\common\controller;

class Log
{
    static function record($code, $op, $ip, $reqInfo)
    {
        $redis = Redis::getRedisHandler();
        $data = [
            'code'  =>  $code,
            'op'    =>  $op,
            'ip'    =>  $ip,
            'info'  =>  $reqInfo,
            'time'  =>  time()
        ];
        $redis->lPush('VproAuthLog', json_encode($data));
    }
}