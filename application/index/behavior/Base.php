<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 5/31/2018
 * Time: 9:57 AM
 */
namespace app\index\behavior;

use app\api\UserApi;
use think\Controller;
use think\Request;

class Base extends controller
{
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        if(request()->isGet())
        {
            $get_do = Request::instance()->get('do');
            if($get_do && method_exists($this, $get_do))
            {
                $this->$get_do();
            }
        }
    }
    function logout()
    {
        $_SESSION = [];
        setcookie('PHPSESSID', '', time() - 3600, '/');
        $this->redirect('/', 1, '正在注销...');
        exit();
    }
}