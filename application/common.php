<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 生成随机字符串
 *
 * @access public
 * @param integer $length 字符串长度
 * @param string $specialChars 是否有特殊字符
 * @return string
 */
 function randString($length=32, $specialChars = false) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    if ($specialChars) {
        $chars .= '!@#$%^&*()';
    }

    $result = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, $max)];
    }
    return $result;
}
/**
 * 获取当前IP函数。
 * @return string
 */
function getIP () //取IP函数
{
    global $_SERVER;
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('REMOTE_ADDR')) {
        $ip = getenv('REMOTE_ADDR');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * 检查字符串是否是
 * @param $value
 * @return bool
 */
function verifyString($value)
{
    // ?!表示在这个位置后面不能出现这个正则：
    // 第一个括号代表这个字符串不能完全由非字母组成
    // 第二个括号代表不能完全由数字组成
    // 第三个括号代表不能完全由特殊符号组成
    // 第四个括号代表不能以大小写字母数字组成
    // 第五个括号代表不能由大小写字母特殊符号组成
    // 第六个括号代表不能由数字特殊符号组成
    // 必须包含字母数字特殊符号
    return !!(preg_match("/^(?![a-zA-z]+$)(?!\d+$)(?![!@#$%^&*]+$)(?![a-zA-z\d]+$)(?![a-zA-z!@#$%^&*]+$)(?![\d!@#$%^&*]+$)[a-zA-Z\d!@#$%^&*]+$/", $value));
}
function genCartId($userId)
{
    $mtime = explode(' ', microtime());
    return '26' . $userId . $mtime[1] . sprintf('%03d', ($mtime[0] * 1000)) . sprintf('%02d', rand(0, 99));
}
