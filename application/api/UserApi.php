<?php
namespace app\api;
use app\common\controller\Log;
use app\common\model\VproAuth;
use think\captcha\Captcha;
use think\Request;
use think\Session;

/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 5/29/2018
 * Time: 1:59 PM
 */

class UserApi
{
    function verifyCode()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }
    function verifyCodeCheck($code, $id='')
    {
        $captcha = new Captcha();
        return $captcha->check($code, $id);
    }
    function login()
    {
        if(!request()->isPost())return false;
        $input = Request::instance()->post();
        $decrypt = [];
        foreach($input as $key => $value)
        {
            $decrypt[$key] = $this->decrypt($value);
        }
//        if (!$this->verifyUserFormat($decrypt['username'])) return false;
        $user = [];
        $user = VproAuth::get(['auth_appid' => $decrypt['username']])->toArray();
        if (!count($user) > 0)
        {
            Log::record(config('OP_LOGIN'), config('LOGIN_USER_NOT_EXIST'), getIP(), $decrypt['username']);
//            return 'user does not exist';
            return false;
        }
        if ($this->decrypt($user['auth_appkey']) === $decrypt['password'])
        {
//            $token = randString();
            $_SESSION = [
                'id'            =>  $user['auth_id'],
                'username'      =>  $user['auth_appid'],
                'ip'            =>  getIP(),
//                'token'         =>  $token,
//                'time'          =>  time()
            ];
            Log::record(config('OP_LOGIN'), config('LOGIN_SUCCESS'), getIP(), $input);
            return $_SESSION;
        }
        else
        {
            return false;
        }
    }
    function verifyUserFormat($str)
    {
        if (!verifyString($str))
        {
            Log::record(config('OP_LOGIN'), config('LOGIN_USER_FORMAT_ERROR'), getIP(), $str);
            return false;
        }
        return true;
    }
//    function isLogin()
//    {
//        if (count($_SESSION) !== 0 && isset($_SESSION['ip']) && $_SESSION['ip'] === getIP())
//        {
//            return $_SESSION;
//        }
//        return false;
//    }
    function isLogin()
    {
        if (
            session('?ip') && session('ip') === getIP()
//            && session('?time') && session('time') > time()
        )
        {
            return $_SESSION;
        }
        return false;
    }
    function getUser()
    {

    }
    function decrypt($data)
    {
        $pi_key = openssl_pkey_get_private(config('PRIVATE_KEY'));//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key = openssl_pkey_get_public(config('PUBLIC_KEY'));//这个函数可用来判断公钥是否是可用的
        $decrypt = '';
        openssl_private_decrypt(base64_decode($data), $decrypt, $pi_key);//私钥解密
        return $decrypt;
    }
}