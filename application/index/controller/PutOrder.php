<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/9
 * Time: 21:06
 */
namespace app\index\controller;

use app\api\CouponApi;
use app\api\CourseApi;
use app\api\OrderApi;
use app\common\controller\Base;
use app\common\controller\ReturnFormat;
use think\Request;

class PutOrder extends Base
{
    public function index()
    {
        $orderId = Request::instance()->route('orderId');
        $courseApi = new CourseApi();
        $orderApi = new OrderApi();
        $courseIds = $orderApi->getOrderCourseIds($orderId);

        if (!$courseIds) {
            header('Location: /cart');
            exit();
        }
        $courses = [];
        $sumPrice = 0;
        foreach($courseIds as $id)
        {
            $res = [];
            $course['course'] = $courseApi->getCourseInfo($id, 'course');
            $sumPrice += $course['course']['course_price'];
            $course['cover'] = $courseApi->getCourseInfo($id, 'cover');
            $course['auth'] = $courseApi->getCourseInfo($course['course']['course_author'], 'auth')['auth_appid'];
            $res = array_merge($course['course'], $course['cover'], ['course_author' => $course['auth']]);
            array_push($courses, $res);
        }
        $this->assign('courses', $courses);
        $this->assign('sumPrice', sprintf('%.02f', $sumPrice));
        $this->assign('orderId', $orderId);

        return $this->fetch('/index/cart/placeOrder');
    }
    public function placeOrder()
    {
        $params = Request::instance()->param();
        $orderApi = new OrderApi();

        // 检查order是否还存在于redis
        if (!$courseIds = $orderApi->checkOrderId($params['orderId'])) return ReturnFormat::json('订单不存在或者已过期，请返回购物车重新提交', 'ORDER_EXPIRED');

        // 检查courseId是否和后台一致并且在缓存中
        if (!$orderApi->checkOrderCourseId($courseIds, $params['orderCourses'], $orderApi)) return ReturnFormat::json('订单课程信息非法', 'ORDER_COURSE_NOT_FOUND');

        // 总价
        $sumPrice = 0;

        $courseApi = new CourseApi();

        // 课程信息列表
        $courses = [];
        $orderTitle = '';
        foreach($params['orderCourses'] as $item)
        {
            // 从数据库或缓存拿到课程详细信息
            $course = $courseApi->getCourseInfo($item['courseId'], 'course');
            if (!$course) return ReturnFormat::json('课程不存在', 'COURSE_NOT_EXISTED');
            // 前后台对比课程价格，不一致则非法
            if (floatval($item['coursePrice']) !== floatval($course['course_price'])) return ReturnFormat::json('课程信息非法', 'ORDER_COURSE_NOT_FOUND');
            if (mb_strlen($orderTitle, 'utf-8') < 32)
            {
                $orderTitle .= $course['course_title'];
                $orderTitle = mb_substr($orderTitle, 0, 31, 'utf-8');
            }
            // 课程价格加入总价
            $sumPrice += floatval($course['course_price']);
            array_push($courses, $course);
        }
        $couponInfo = [];
        // 判断优惠券信息
        if ($params['coupon'] !== -1) {
            $couponApi = new CouponApi();

            // 查看用户是否拥有该优惠券，没有就是非法
            if ($couponApi->checkUserHasCoupon($params['coupon']['coupon_id'], session('authId'))) return ReturnFormat::json('优惠券资格非法！', 'COUPON_USER_ILLEGAL');
            // 从数据库或者缓存中生成优惠券信息
            $couponInfo = $couponApi->genCouponInfo($params['coupon']['coupon_id']);
            // 优惠券没找着，不存在，非法
            if (!$couponInfo) return ReturnFormat::json('优惠券非法', 'COUPON_NOT_EXIST_ILLEGAL');
            // 优惠券信息前后台不符，非法
            if (count(array_diff_assoc($couponInfo, $params['coupon']))) return ReturnFormat::json('优惠券信息错误', 'COUPON_INFO_ILLEGAL');
            // 检查优惠券在本订单是否可用
            if (!$couponApi->checkCouponForOrder($sumPrice, $couponInfo)) return ReturnFormat::json('优惠券不可用', 'COUPON_NOT_MEET_REQUIREMENT');
            // 扣除优惠金额
            $sumPrice -= floatval($couponInfo['coupon_discount']);
        }
        // 前后台总价不同，非法
        if ($sumPrice !== floatval($params['sumPrice'])) return ReturnFormat::json('总价不符', 'ORDER_PRICE_MISMATCH');
        $orderObj = [
            'orderId'       =>      $params['orderId'],
            'orderTitle'    =>      $orderTitle,
            'orderPrice'    =>      (float)$sumPrice,
            'authId'        =>      (int)session('id'),
            'coupon'        =>      $couponInfo,
            'courses'       =>      $courses
        ];
        $validate = validate('Order');
        if (!$validate->check($orderObj))
        {
//            $validate->getError()
            return ReturnFormat::json('下单系统错误，建议联系网站', 'ORDER_OBJ_VALIDATE_ERROR');
        }
        if ($orderApi->placeOrder($orderObj))
        {
            $pay = new PayOrders();
            return $pay->pcpay([
                'out_trade_no'  =>  (string)$params['orderId'],
                'total_amount'  =>  (string)$sumPrice,
                'subject'       =>  (string)$orderTitle,
            ]);
        }
        return ReturnFormat::json('下单系统错误，建议联系网站', 'ORDER_PUT_DATABASE_ERROR');
    }
}