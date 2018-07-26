<?php
/**
 * Created by PhpStorm.
 * User: szl4zsy
 * Date: 7/10/2018
 * Time: 4:30 PM
 */
namespace app\api;

use think\Db;
use think\Request;

class OrderApi extends BaseApi {
    private $orderPrefix = 'orders';
    public function getOrderCourseIds($orderId) {
        $courseIds = json_decode($this->redis->get($orderId));
        // 订单已经超时了，商品id已经过期
        if (!$courseIds) return false;
        return $courseIds;
    }
    public function getSumPrice($courseIds)
    {
        $sumPrice = 0;
        $courseApi = new CourseApi();
        foreach($courseIds as $courseId)
        {
            $course = $courseApi->getCourseInfo($courseId, 'course');
            $sumPrice += floatval($course['course_price']);
        }
        return $sumPrice;
    }
    public function checkOrderId($orderId)
    {
        return json_decode($this->redis->get($orderId));
    }
    public function checkOrderCourseId(array $courseIds, array $courses):bool
    {
        $courseApi = new CourseApi();
        $frontCourseIds = array_map(function($item) {
            return $item['courseId'];
        }, $courses);
        if (count(array_diff($courseIds, $frontCourseIds)))
        {
            return false;
        }
        foreach($frontCourseIds as $id)
        {
            if (!$courseApi->checkCourseExists($id)) return false;
        }
        return true;
    }
    public function placeOrder(array $orderObj)
    {
        if (count($orderObj) === 0) return false;
        $couponIsUsed = 0;
        $discount = 0;
        if (count($orderObj['coupon']) > 0)
        {
            $couponIsUsed = $orderObj['coupon']['coupon_id'];
            $discount = $orderObj['coupon']['coupon_discount'];
            // 优惠券已被使用，记录到redis中
            $this->redis->setBit($this->userCouponUsedPrefix . $orderObj['authId'], $orderObj['coupon'], 1);
        }
        $coursesData = [];
        foreach($orderObj['courses'] as $course)
        {
            array_push($coursesData, [
                'order_id'      =>  $orderObj['orderId'],
                'course_id'     =>  $course['course_id'],
                'course_price'  =>  $course['course_price']
            ]);
        }
        Db::startTrans();
        try{
            // 生成主订单
            Db::table('vpro_order')->insert([
                'order_id'              =>      $orderObj['orderId'],
                'order_price'           =>      $orderObj['orderPrice'],
                'order_time'            =>      time(),
                'user_id'               =>      $orderObj['authId'],
                'order_coupon_used'     =>      $couponIsUsed,
                'order_discount'        =>      $discount,
                'order_payment'         =>      0,
                'order_title'           =>      $orderObj['orderTitle'],
                'order_payment_id'      =>      null,
                'order_payment_price'   =>      0
            ]);
            // 生成子订单
            Db::table('vpro_order_sub')->insertAll($coursesData);
            Db::commit();

            // 记录操作日志

            return true;
        } catch(\Exception $e) {
            var_export($e->getMessage());
            // 记录失败日志
            Db::rollback();
            return false;
        }
    }
    public function recordOrderTime($orderId)
    {
        $this->redis->setex($this->orderPlaceTimePrefix . $orderId, config('key.ORDER_EXPIRED_TIME'), time());
    }
    public function delRecordOrderTime($orderId)
    {
        $this->redis->del($this->orderPlaceTimePrefix . $orderId);
    }

    public function getOrders(int $type)
    {
        // 0 待付款 1 支付成功 2 交易关闭
        $type = intval(Request::instance()->route('type', '1'));
        $orders = $this->getOrdersData($type);
        if (!count($orders)) return [];
        foreach($orders as $key => $item)
        {
            $orders[$key]['order_sub'] = $this->getOrderDetail($item['order_sub']);
        }
        return $orders;

    }
    public function getOrdersData($type)
    {
        if (!$this->redis->exists($this->orderPrefix . session('id'))) {
            $res = [];
            $vproOrder = Db::table('vpro_order')
                ->alias('vo')
                ->join('vpro_order_sub vos', 'vos.order_id = vo.order_id', 'left')
                ->where('vo.user_id', session('id'))
                ->select();
            if (count($vproOrder)) {
                foreach ($vproOrder as $item) {
                    if (!array_key_exists($item['order_id'], $res)) {
                        $res[$item['order_id']] = [
                            'order_id' => $item['order_id'],
                            'order_price' => $item['order_price'],
                            'order_time' => $item['order_time'],
                            'user_id' => $item['user_id'],
                            'order_coupon_used' => $item['order_coupon_used'],
                            'order_discount' => $item['order_discount'],
                            'order_payment' => $item['order_payment'],
                            'order_payment_id' => $item['order_payment_id'],
                            'order_payment_price' => $item['order_payment_price'],
                            'order_sub' => ''
                        ];
                    }
                    $res[$item['order_id']]['order_sub'] .= $res[$item['order_id']]['order_sub'] === '' ? $item['course_id'] : ',' . $item['course_id'];
                }
            }
            $this->redis->hMset($this->orderPrefix . session('id'), array_map(function($item) {
                return json_encode($item);
            }, $res));
        }

        $orders = array_map(function($item) {
            return json_decode($item, true);
        }, $this->redis->hGetAll($this->orderPrefix . session('id')));
        if (!$orders) return [];
        return array_filter($orders, function($item) use ($orders, $type) {
            return $item['order_payment'] === (int)$type;
        });
    }
    function getOrderDetail($orderSub)
    {
        $courseApi = new CourseApi();
        $res = [];
        $sub = explode(',', $orderSub);
        foreach($sub as $item)
        {
            $course = $courseApi->getCourseInfo($item, 'course');
            $cover = $courseApi->getCourseInfo($item, 'cover');
            $res[] = [
                'course_id'             =>      $course['course_id'],
                'course_price'          =>      $course['course_price'],
                'course_title'          =>      $course['course_title'],
                'course_author'         =>      $course['course_author'],
                'course_cover_address'  =>      $cover['course_cover_address']
            ];
        }
        return $res;
    }
}