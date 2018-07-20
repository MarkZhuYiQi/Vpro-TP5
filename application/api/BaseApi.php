<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 6/29/2018
 * Time: 10:40 AM
 */
namespace app\api;
use app\common\controller\Redis;
class BaseApi
{
    // 优惠券存放地址前缀
    protected $couponPrefix = 'coupon';
    // 用户优惠券存放地址前缀
    protected $userCouponPrefix = 'userCoupon';
    // 用户优惠券存放是否还存在地址前缀
    protected $userCouponIsExistedPrefix = 'userCouponIsExisted';
    // 用户有效优惠券地址存放前缀
    protected $validUserCouponPrefix = 'validUserCoupon';
    // 用户优惠券使用地址存放前缀
    protected $userCouponUsedPrefix = 'userCouponUsed';
    protected $authId = -1;
    protected $redis;

    protected $cartPrefix = 'cart';
    function __construct()
    {
        $this->redis = Redis::getRedisHandler();
    }
}