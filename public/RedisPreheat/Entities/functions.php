<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:01 AM
 */


const defaultCache = "-1";          //默认缓存,字符可以自己设置
const forbiddenLimit = 6;           //设置 非命中最大值，超过这个数 直接不能获取 内容
const forbiddenIPListLimit = 3;     //IP段List中 最大长度
const forbiddenExpire = 3600;       //incr后，重新设置过期时间,一般为一天

//上面这些玩意儿，最好写在一个config.php里面

require("RedisObject.php");
require("Db.php");
// redis相关函数
$redis = new app\Entities\RedisObject();

function isInForbiddenIPList(String $key)//IP段是否被禁止
{
    global  $redis;
    $len=$redis->ListOperator->len($key);
    if($len>=forbiddenIPListLimit)
        return true;
    return false;
}
function isForbidden(String $key) //单个IP是否已经被禁止
{
    global  $redis;

    $getNum=$redis->StringOperator->getFromReids($key);
    if($getNum && intval($getNum)>=forbiddenLimit)
    {

        return true;
    }
    return false;
}

function getParameter(String $p):int
{
    if(isset($_GET[$p]))
        return $_GET[$p];
    return 0;
}
function getFromDB(String $newsid)
{
    $db=new app\Entities\Db();
    return $db->getData(["news_id"=>$newsid],"news");
}
function getDataBySQL(String $sql)//为了演示简单， 直接根据sql取数据
{
    $db=new app\Entities\Db();
    return $db->getDataBySql($sql);
}

function isDefaultCache($c):bool
{
    if(trim($c)==="-1")
        return true;
    return false;
}
function getIPLevel(String $ip,int $level=3) // 根据IP获取 前N段内容,默认3段 ,如192.168.10.3 只取192.168.10   目前只支持IPV4
{
    $ip=explode(".",$ip); //拆分成数组
    $ret="";
    for($i=0;$i<=$level-1;$i++)
    {
        if($ret!="") $ret.=".";
        $ret.=$ip[$i];
    }
    return $ret;


}
function getIP(){ //获取客户端IP的函数

    //为了测试 ，直接返回某个IP
    return "192.168.10.2";

    if(isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    }else{
        if(getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv( "HTTP_X_FORWARDED_FOR");
        }elseif(getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        }else{
            $realip = getenv("REMOTE_ADDR");
        }
    }

    return $realip;
}

