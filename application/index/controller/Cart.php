<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/3/2018
 * Time: 1:29 PM
 */
namespace app\index\controller;

use app\api\CartApi;
use app\api\UserApi;
use app\common\controller\Base;
use think\Cookie;
use think\Request;

class Cart extends Base
{
    function index()
    {
        $cart = [];
        $user = new UserApi();
        $cartApi = new CartApi();
        if (!$user->isLogin())
        {
            $cart = [];
            if (Cookie::get('cart'))
            {
                foreach(Cookie::get('cart') as $item)
                {
                    array_push($cart, [
                        'course_id'             =>  $item['course_id'],
                        'course_title'          =>  $item['course_title'],
                        'course_pid'            =>  $item['course_pid'],
                        'auth_appid'            =>  $item['course_author'],
                        'course_price'          =>  $item['course_price'],
                        'course_cover_address'  =>  $item['course_cover_address'],

                    ]);
                }
            }
//            $this->redirect('/index/user?r=' . request()->url());
        } else {
            // 合并cookie和数据库中的商品
            $cartApi->mergeCart();
            $cart = $cartApi->getCartItems(session('id'));
//            $cart = $cartApi->getCartItems(1);
        }
        $this->assign('cart', $cart);
        $this->assign('cartNum', count($cart));
        return $this->fetch('/index/cart/index');
    }
    public function checkCartItem()
    {
        $cartApi = new CartApi();
        $courses = Request::instance()->param();
        $res = $cartApi->checkCourses($courses);
        if (count($res['errs']) === 0) $res['orderNo'] = $cartApi->prePlaceOrder($res['courses']);
        echo json_encode($res);
    }
}