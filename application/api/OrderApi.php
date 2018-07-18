<?php
/**
 * Created by PhpStorm.
 * User: szl4zsy
 * Date: 7/10/2018
 * Time: 4:30 PM
 */
namespace app\api;

class OrderApi extends BaseApi {
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
        return $this->redis->get($orderId);
    }
    public function placeOrder()
    {

    }
    public function checkOrderCourseId(array $courseIds, array $courses):bool
    {
        $courseApi = new CourseApi();
        $frontCourseIds = array_filter($courses, function($item) {
            return $item['courseIds'];
        });
        if (array_diff($courseIds, $frontCourseIds))
        {
            return false;
        }
        foreach($frontCourseIds as $id)
        {
            if (!$courseApi->checkCourseExists($id)) return false;
        }
        return true;
    }
}