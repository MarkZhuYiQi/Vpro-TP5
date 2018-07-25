<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/20/2018
 * Time: 3:23 PM
 */
namespace app\index\controller;

use app\common\controller\Base;

class Orders extends Base
{
    function index()
    {
        $this->redirect('/index/user?r=' . request()->url());
        return $this->fetch('index/orders/index');
    }
}