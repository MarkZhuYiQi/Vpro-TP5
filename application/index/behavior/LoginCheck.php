<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/9/2018
 * Time: 10:20 AM
 */
namespace app\index\behavior;
use app\api\UserApi;
class LoginCheck extends Base
{
    /**
     * @param $params
     * 判断客户是否登录流程：
     * 首先是这个钩子，在调用action的时候调用这个方法
     * 进入isLogin, 判断当前访问者的session在缓存里是否存在，是否存在ip，ip是否相同
     * 如果有就说明存在，返回session，不存在返回false
     * 返回的变量交给模板，模板判断显示哪一组页面
     */
    public function run(&$params)
    {
        $user = new UserApi();
        var_export(123321);
        return $user->isLogin();
    }
}