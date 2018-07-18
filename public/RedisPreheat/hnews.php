<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:13 AM
 */
namespace app;

require 'Entities/functions.php';
//require 'Entities/NewsInfo.php';

$courseId = getParameter('id');
if (!$courseId) die('error parameters');
$coursesKey_prefix = 'hCourses';   // 课程前缀
$coursesKey = $coursesKey_prefix . $courseId;

//$news = new NewsInfo();

$getCourse = $redis->hashOperator->get($coursesKey);
var_export($getCourse);
