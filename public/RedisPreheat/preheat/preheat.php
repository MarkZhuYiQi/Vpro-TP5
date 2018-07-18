<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:41 AM
 */
require_once './../Entities/functions.php';
$cmd = 'init';
if ($argv === 2)
{
    $cmd = $argv[1];
}
switch ($cmd) {
    case 'init':
        pre_init();
        break;
    default:
        break;
}
function pre_init()
{
    global $redis;
    $redis->pipeExec(function ($redis) {
        $coursesData = getDataBySQL('select * from vpro_courses order by course_id');
        foreach($coursesData as $key => $value)
        {
            $key = 'VproCourses' . $value['course_id'];
            $cacheKey = 'VproCoursesCache';
            $redis->hMset($key, [
                'course_id'              =>      $value['course_id'],
                'course_title'           =>      $value['course_title'],
                'course_pid'             =>      $value['course_pid'],
                'course_author'          =>      $value['course_author'],
                'course_cover'           =>      $value['course_cover'],
                'course_time'            =>      $value['course_time'],
                'course_status'          =>      $value['course_status'],
                'course_price'           =>      $value['course_price'],
                'course_discount_price'  =>      $value['course_discount_price']
            ]);
            // 不设置缓存，通过cache键值来控制
//            $redis->expire($key,3600*24*7);
            // 插入时间戳
            $redis->zAdd($cacheKey, time(), $value['course_id']);
        }
        $redis->exec();
        echo 'done~~';
    });
    $redis->pipeExec(function($redis) {
        $tempData = getDataBySQL('select * from vpro_courses_temp_detail order by course_id');
        foreach($tempData as $key => $value)
        {
            $key = 'VproTempDetail' . $value['course_id'];
            $cacheKey = 'VproTempDetailCache';
            $redis->hMset($key, [
                'course_id'         =>      $value['course_id'],
                'course_pid'        =>      $value['course_pid'],
                'course_score'      =>      $value['course_score'],
//              单独列出点击量
//                'course_clickNum'   =>      $value['course_clickNum'],
            ]);
            $redis->zAdd('courses_click', $value['course_clickNum'], 'course' . $value['course_id']);

            // 不设置缓存，通过cache键值来控制
//            $redis->expire($key, 3600*24*7);
            // 插入时间戳
            $redis->zAdd($cacheKey, time(), $value['course_id']);
        }
        $redis->exec();
    });
    $redis->pipeExec(function($redis) {
        $tempData = getDataBySQL('select * from vpro_courses_cover order by course_cover_id');
        foreach($tempData as $key => $value)
        {
            $key = 'VproCoursesCover' . $value['course_cover_id'];
            $cacheKey = 'VproCoursesCoverCache';
            $redis->hMset($key, [
                'course_cover_id'       =>      $value['course_cover_id'],
                'course_cover_key'      =>      $value['course_cover_key'],
                'course_cover_address'  =>      $value['course_cover_address'],
                'course_cover_uptime'   =>      $value['course_cover_uptime'],
                'course_cover_isuploaded'   =>      $value['course_cover_isuploaded'],
                'course_cover_isused'   =>      $value['course_cover_isused'],
            ]);
//            $redis->expire($key, 3600*24*7);
            // 插入时间戳
            $redis->zAdd($cacheKey, time(), $value['course_cover_id']);
        }
        $redis->exec();
    });
    $redis->pipeExec(function($redis) {
        $tempData = getDataBySQL('select * from vpro_auth order by auth_id');
        foreach($tempData as $key => $value)
        {
            $key = 'VproAuth' . $value['auth_id'];
            $cacheKey = 'VproAuthCache';
            $redis->hMset($key, [
                'auth_id'           =>  $value['auth_id'],
                'auth_appid'        =>  $value['auth_appid'],
                'auth_appkey'       =>  $value['auth_appkey'],
                'auth_roles_id'     =>  $value['auth_roles_id']
            ]);
            $redis->expire($key, 3600*24*7);
        }
        $redis->exec();
    });
}