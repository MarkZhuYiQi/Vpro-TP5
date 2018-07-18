<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/11
 * Time: 23:14
 */
namespace app\index\controller;

use app\api\CouponApi;
use app\api\OrderApi;
use app\common\controller\Base;
use app\common\controller\ReturnFormat;
use think\Request;

class Coupon extends Base{
    function getCoupon()
    {
        $orderId = Request::instance()->route('orderId');
        // 1，可使用；2，不可使用；3，已过期
        $type = Request::instance()->route('type', '1');
        $authId = session('id');
        if (!$authId) echo ReturnFormat::json('请重新登录', 'SESSION_EXPIRED');
        if (!$orderId || !$authId) echo ReturnFormat::json('信息不全', 'PARAMS_MISSING');
        $orderApi = new OrderApi();
        if (!$orderApi->checkOrderId($orderId)) {
            echo ReturnFormat::json('订单已过期', 'ORDER_EXPIRED');
            exit();
        }
        $sumPrice = $orderApi->getSumPrice($orderApi->getOrderCourseIds($orderId));
        $couponApi = new CouponApi();
        // 该用户所有优惠券
        $coupons = $couponApi->getCoupon($authId);
        if(!$coupons) echo ReturnFormat::json('无可用优惠券', 'COUPON_NONE');
        // 该用户目前可用优惠券
        $validateCoupons = $couponApi->validateCoupon($coupons);
        if (!count($validateCoupons)) {
            echo ReturnFormat::json('无可用优惠券', 'COUPON_NONE');
        } else {
            echo ReturnFormat::json($couponApi->checkCouponForOrder($sumPrice, $coupons));
        }
    }
}