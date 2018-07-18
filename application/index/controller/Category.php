<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 6/28/2018
 * Time: 3:38 PM
 */
namespace app\index\controller;

use app\api\CategoryApi;
use app\api\NavApi;
use app\common\controller\Base;

use think\Request;

class Category extends Base
{
    public function index()
    {
//        $indexApi = new IndexApi();
        $params = Request::instance()->route();
        if (count($params) && array_key_exists('nav', $params))
        {
            $navApi = new NavApi();
            $navId = $navApi->getNavId($params['nav']);
            $navs = $navApi->getSubNavs($navApi->genNavTree(), $navId);
            $navIds = $navApi->getAllNavIds($navs);

            $categoryApi = new CategoryApi();

            $coursesTotal = $categoryApi->getTotalCourses($navIds);

            $pageCount = ceil($coursesTotal / config('index.COURSES_OFFSET'));

            if (!array_key_exists('page', $params) || $params['page'] === null) $params['page'] = 1;
            $courses = $categoryApi->getCourses($navIds, $params['page']);
            $this->assign('coursesInfo', $courses);
            $this->assign('currentPage', $params['page']);
            $this->assign('pageCount', $pageCount);

            return $this->fetch('index/category/index');
        }
    }
}