<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 6/29/2018
 * Time: 3:07 PM
 */
return [
    // 全局
    'PARAMS_MISSING'                            =>          12001,
    'SESSION_EXPIRED'                           =>          12101,
    'SUCCESS'                                   =>          20000,

    // 安全相关
    'DB_MISS'                                   =>          2,  // 恶意积分
    'REDIS_MISS'                                =>          1,
    'SCORE_EXPIRE'                              =>          20,   // 恶意积分缓存时间
    'MISS_THRESHOLD'                            =>          30,
    'IP_FORBIDDEN'                              =>          10666,   // ip禁止访问代码

    // api相关

    // 优惠券信息

    // 没有可用优惠券
    'COUPON_NONE'                               =>          30080,
    // 没有找到这张优惠券
    'COUPON_NOT_EXIST_ILLEGAL'                  =>          30081,
    // 用户没有该优惠券资格
    'COUPON_USER_ILLEGAL'                       =>          30082,
    // 优惠券信息前后台不符
    'COUPON_INFO_ILLEGAL'                       =>          30083,
    // 用户订单无法满足优惠券要求
    'COUPON_NOT_MEET_REQUIREMENT'               =>          30084,
    // 前后台总结不同
    'ORDER_PRICE_MISMATCH'                      =>          30085,

    // 订单相关
    'ORDER_EXPIRED'                             =>          35001,
    'ORDER_COURSE_NOT_FOUND'                    =>          35002,
    'COURSE_NOT_EXISTED'                        =>          35003,
    'ORDER_OBJ_VALIDATE_ERROR'                  =>          35004,
    'ORDER_PUT_DATABASE_ERROR'                  =>          35005,
    'ORDER_EXPIRED_TIME'                        =>          1800,

    // pay相关

    // 购物车相关
    'CART_COURSE_INFO_ERROR'                    =>          36001,

    // 订单分页：
    'ORDERS_PAGINATION_COUNT'                   =>          10,
];