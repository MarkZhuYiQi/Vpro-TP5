<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'   => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    // 路由匹配，加[]代表是可选变量，否则必须有值才能匹配，后面写上真实地址，请求方式，请求方式和参数过滤
//    ''
    'category/[:nav]/[:page]'   =>  ['index/category/index', ['method' => 'get'], ['nav' => '([a-zA-Z\-\+]+)|(\d+)', 'page' => '\d+']],
    'detail/:courseId'          =>  ['index/detail/index', ['method' => 'get'], ['courseId' => '\d+']],
    'detail/click/:courseId'    =>  ['index/detail/addClickNum', ['method' => 'get'], ['courseId' => '\d+']],
    'cart$'                     =>  ['index/cart/index', ['method' => 'get']],
    'cart/checkcartitem'        =>  ['index/cart/checkCartItem', ['method' => 'post']],
    'putorder/[:orderId]'       =>  ['index/PutOrder/index', ['method' => 'get'], ['orderId' => '\d{19}']],
    'putorder/place_order'      =>  ['index/PutOrder/placeOrder', ['method' => 'post']],
    'coupon/:orderId/[:type]'   =>  ['index/coupon/getCoupon', ['method' => 'get'], ['orderId' => '\d{19}', 'type' => '\d{1}']],
    'orders'                    =>  ['index/Orders/index', ['method' => 'get']]
];
