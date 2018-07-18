<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/5/24
 * Time: 21:14
 */
namespace app\index\controller;

use app\api\IndexApi;
use app\common\controller\Base;

class Index extends Base {
    public function index()
    {
        $indexApi = new IndexApi();
        $res = $indexApi->indexInfo();
        $ret = [];
        $navs = $res['nav'];
        $courses = (array)$res['courses'];
        // 将原本导航课程分离的表组合成导航下的indexCourses用于显示
        foreach($navs->children as $nav)
        {
            $nav->indexCourses = $courses[$nav->nav_id];
            array_push($ret, $nav);
        }
        $this->assign('indexInfo', $ret);
        return $this->fetch('index/index/index');
    }
    public function hello()
    {
        $indexApi = new IndexApi();
        $indexApi->init();
//        var_export($indexApi->indexInfo());
//        exit();
        $res = $indexApi->indexInfo();
        $ret = [];
        $navs = $res['nav'];
        $courses = (array)$res['courses'];
        foreach($navs->children as $nav)
        {
            $nav->indexCourses = $courses[$nav->nav_id];
            array_push($ret, $nav);
        }
    }
    public function info()
    {
        phpinfo();
    }
}