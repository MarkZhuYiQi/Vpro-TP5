<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/9
 * Time: 21:06
 */
namespace app\index\controller;

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
        if (!$courseIds = $orderApi->checkOrderId($params['orderId'])) return ReturnFormat::json('订单不存在或者已过期，请返回购物车重新提交', 'ORDER_EXPIRED');
        $courseApi = new CourseApi();
        $sumPrice = 0;
        foreach($params['orderCourses'] as $item)
        {
            if (!($course = $courseApi->checkCourseExists($item['courseId']))) return ReturnFormat::json('课程信息未知', 'ORDER_COURSE_NOT_FOUND');
            if (floatval($item['coursePrice']) !== floatval($course['course_price'])) return ReturnFormat::json('课程信息非法', 'ORDER_COURSE_NOT_FOUND');
            $sumPrice += floatval($course['course_price']);
        }
        if ($sumPrice !== floatval($params['sumPrice']))

    }
    public function checkOrderCourseId($courseIds, $courses)
    {
        $frontCourseIds = array_filter($courses, function($item) {
            return $item['courseIds'];
        });
        if (array_diff($courseIds, $frontCourseIds))
        {
            return false;
        }
        foreach($frontCourseIds as $id)
        {
            
        }
    }
}