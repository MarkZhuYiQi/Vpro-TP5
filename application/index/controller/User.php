<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 5/29/2018
 * Time: 1:56 PM
 */
namespace app\index\controller;

use app\api\UserApi;
use app\common\controller\Base;
use think\captcha\Captcha;
use think\Controller;
use think\Request;

class User extends Base
{
    public function index()
    {
//        $captcha = new Captcha();
//        return $captcha->entry();
        $this->assign('errorInfo', '123');
        return $this->fetch('index/user/login');
    }
    public function login()
    {
        $user = new UserApi();
        if ($user->login())
        {
            $aim = Request::instance()->get('r');
            return $aim;
        }
        return false;
    }
    public function verifyCode()
    {
        $user = new UserApi();
        return $user->verifyCode();
    }

    public function verifyCodeCheck()
    {
        if(!request()->isPost())return false;
        $post = Request::instance()->post();
        $user = new UserApi();
        return $user->verifyCodeCheck($post['code']);
    }

    public function user()
    {
        return $this->fetch('index/user/test');
    }


    public function test()
    {
        if(request()->isPost())
        {
            $input = Request::instance()->post();
            $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDwYV4IPkfNau3aUVojBYAlTH0ZK+4qGxYpmUMvbVy/cPBl++LN
Zjxa18IDvrbmeBUIJK3KwbTq8STA6bEPWQUtCU7Z+gGPuzmOJDFUttRrkHNcgA1R
nBwfdSg0x4wVN0vwnNYn1Wzni9urTC3weDEYTLpF/DBPATaN1lnCFzwciwIDAQAB
AoGAIWN02zJDaO30UcHBAmJONWAVdDyc3S5b+rU2Fs1a96BpU9ET5LHRqlCTu09v
Oj3tte7aUPJs/cE2+LC9DkOwKxdXIqadj4sFgGdOmH68Q2S+PHV5i2GYwoRUNxdJ
J8At9BNt4Cy+1GXHHCNX5C1wbnS0CeWiKMxuAgvlsG+31OkCQQD7vemziM/wn4fo
1MtqMvLLS8O/g1RQ01IVy/xii35wL3GhZpsMFZlRRNtCXNi9RjAWdIwtDNkNrhV0
SdMCtAc1AkEA9HJBz0g7thlZ/IHqtOqH1/qmrlDDlSFfBFW9g0iNvbLVDf9+Fsph
FQDH7SGCOJvF5g2FHdoiC4bwMB+6u4tMvwJARv5N09W3XpJ+z4iDPRXVJsPdFjtB
IfIWahM2v8u7AoQ+tVesTgIhVKvocZShgu8yTILdrS68X4FCh6LyIQcIKQJAUat6
4U445PZDYmHlkNxq1nYgCk1hiwnDPSeIUbyD3sVI+YxLDEJBfUrtgQSZBWDGFb6e
owKmLUPAK9PuB4ra8QJBALH0XzyJb5cdOCgiTKZTjevMft62Sysy0mCBCt9pjmwU
rnNmJz3ShQiEBFOdE1r73GB6KS+RXJG1LzD2naniyVk=
-----END RSA PRIVATE KEY-----';

// 公钥一般存放在登录页面中的一个隐藏域中，但是请注意：公钥和私钥一定要配对，且必须保证私钥的安全
            $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDwYV4IPkfNau3aUVojBYAlTH0Z
K+4qGxYpmUMvbVy/cPBl++LNZjxa18IDvrbmeBUIJK3KwbTq8STA6bEPWQUtCU7Z
+gGPuzmOJDFUttRrkHNcgA1RnBwfdSg0x4wVN0vwnNYn1Wzni9urTC3weDEYTLpF
/DBPATaN1lnCFzwciwIDAQAB
-----END PUBLIC KEY-----';

            /**
             * 使用PHP OpenSSL时，最好先看看手册，了解如何开启OpenSSL 和 其中的一些方法的使用
             *  具体如何使用这里不做赘述，大家去看看PHP手册，什么都就解决了
             */
            $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
            $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
            $decrypted = "";
            openssl_private_decrypt(base64_decode($input['password']),$decrypted,$pi_key);  //私钥解密
// 这里的这个 $decrypted就是解密客户端发送过来的用户名，至于后续连接数据库验证登录信息的代码，这里也就省略了
            return json_encode($decrypted);
        }
    }
}