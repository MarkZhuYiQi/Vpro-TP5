<?php
namespace app\index\controller;

use app\api\CartApi;
use app\api\CourseApi;
use app\api\UserApi;
use app\common\controller\Base;
use think\Loader;
use think\Request;

/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/2/2018
 * Time: 3:10 PM
 */
class Detail extends Base
{
    public function index()
    {
        $params = Request::instance()->route();
        if (count($params) > 0 && $params['courseId'] !== null)
        {
            $courseApi = new CourseApi();
            $courseApi->setCourseClickNum($params['courseId']);
            $list = $courseApi->getCourseList($params['courseId']);
            $lessonList = $courseApi->genCourseList($list);
            $courseDetail = $courseApi->getCourseDetail($params['courseId']);
            $this->assign('courseDetail', $courseDetail);
            $this->assign('lessonList', $lessonList);
        }
        return $this->fetch('index/detail/index');
    }
    public function addToCart()
    {

        if (Request::instance()->isGet() && Request::instance()->has('course_id')) {
            $course_id = Request::instance()->get('course_id');
            $validate = Loader::validate('User');
            if (!$validate->check(['course_id'  =>  $course_id])) return false;
            $userApi = new UserApi();
            $cartApi = new cartApi();
            if ($userInfo = $userApi->isLogin())
            {
                return $cartApi->addToUserCart($course_id);
            }
            return $cartApi->addToCookieCart($course_id);
        }
    }

}