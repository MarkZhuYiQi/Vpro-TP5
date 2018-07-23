<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 6/29/2018
 * Time: 10:40 AM
 */

namespace app\api;
use app\common\model\VproNavbar;
use think\exception\DbException;

class NavApi extends BaseApi
{
    public function getNavId($navKey)
    {
        if (!preg_match("/^(?![\-]+$)([a-zA-Z\d\-]+$)/", $navKey)) return false;
        try {
            $navId = VproNavbar::get(['nav_nickname' => $navKey]);
            // Redis::scorePoint(config('key.DB_MISS'));
            if ($navId === null) return false;
            $navId = $navId->toArray();
            // 这里进行计分，如果db没有命中，说明是没有意义的课程id，涉嫌恶意访问，进行恶意积分累加
            return $navId['nav_id'];
        } catch (DbException $e) {
            var_export($e);
            return false;
        }
    }
    public function getNav()
    {
        try {
            $vproNavbar = VproNavbar::all()->toArray();
        } catch (DbException $e) {

            var_export($e);

        }
        return $vproNavbar;
    }
    public function genNavTree()
    {
        $navTree = [];
        $originNav = $this->getNav();
        foreach ($originNav as $nav)
        {
            if($nav['nav_pid'] === 0)
            {
                $children = $this->_genNavChild($nav, $originNav);
                if(count($children) > 0) $nav['children'] = $children;
                array_push($navTree, $nav);
            }
        }
//        $this->redis->setex('VproNavbar', 3600 * rand(8, 20), json_encode($navTree));
        return $navTree;
    }
    private function _genNavChild($nav, $originNav)
    {
        $res = [];
        foreach($originNav as $v)
        {
            if ($v['nav_pid'] === $nav['nav_id'])
            {
                $children = $this->_genNavChild($v, $originNav);
                if (count($children) > 0) $v['children'] = $children;
                array_push($res, $v);
            }
        }
        return $res;
    }

    /**
     * 获得主导航下的所有子导航
     * @param $res
     * @param int $navId
     */
    public function getSubNavs($res, $navId=9999)
    {
        if($navId === 9999)
        {
            return (array)new class($res)
            {
                public $children = [];
                function __construct($res)
                {
                    $this->children = $res;
                }
            };
        }
        $requestNav = [];
        foreach ($res as $value)
        {
            if ($value['nav_id'] === $navId)
            {
                $requestNav = $value;
                break;
            }
            elseif (array_key_exists('children', $value) && is_array($value['children']))
            {
                $requestNav = $this->getSubNavs($value['children'], $navId);
            }
            if (count($requestNav) > 0) break;
        }
        return $requestNav;
    }
    public function getSubNav()
    {

    }


    public function getAllNavIds($nav, $navIds = [])
    {
        if (array_key_exists('children', $nav) && is_array($nav['children']))
        {
            $navIds = $this->getAllNavIds($nav['children'], $navIds);
        }
        else
        {
            if (is_array($nav))
            {
                foreach($nav as $v)
                {
                    if (array_key_exists('children', $v) && is_array($v['children']))
                    {
                        $navIds = $this->getAllNavIds($v['children'], $navIds);
                    }
                    else
                    {
                        $navIds[] = $v['nav_id'];
                    }
                }
            }
            else
            {
                $navIds[] = $nav['nav_id'];
            }
        }
        return $navIds;
    }

}