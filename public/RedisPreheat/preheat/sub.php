<?php
// 设置php监听超时时间
ini_set('default_socket_timeout', -1);
require('./../Entities/functions.php');
// 超时时间
$cacheTime = 20;
// 需要缓存的信息列表
$cacheKeys = ['VproCoursesCache', 'VproTempDetailCache', 'VproCoursesCoverCache'];
// 供删除缓存时的操作
$prefixes = ['VproCourses', 'VproTempDetail', 'VproCoursesCover'];
// 频道名
$channel = 'courses';

// 频道回调执行的函数
function callback($redis, $chans, $msg)
{
    if ($msg === 'cc')
    {
        clearCache();
    }
}
$redis->subscribe($channel, 'callback');
function clearCache()
{
    global $cacheTime, $cacheKeys;
    global $redis;
    if (is_array($cacheKeys))
    {
        foreach($cacheKeys as $ck)
        {
            // 取出信息列表中所有超过超时时间的id
            $ids = $redis->client()->zrangeByScore($ck, 0, time() - $cacheTime);
            // 进行删除操作
            execDel($ids);
        }
    }
}
function execDel($ids)
{
    global $redis, $prefixes;
    $idsStr = implode(" ", $ids);
    if ($ids && count($ids) > 0) {
        // 执行的回调函数传入外部的变量
        $redis->mutiExec(function (Redis $redis) use ($ids, $prefixes) {
            foreach ($ids as $id) {
                foreach ($prefixes as $prefix) {
                    $redis->del($prefix . $id);
                    $redis->zRem($prefix . 'Cache', $id);
                    if ($prefix === 'courses_click') {
                        $redis->zRem('courses_click', 'course' . $id);
                    }
                }
            }
        });
        echo 'clear cache done'.PHP_EOL;
    } else {
        echo 'no expired cache'.PHP_EOL;
    }
}