<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/20/2018
 * Time: 3:23 PM
 */
namespace app\index\controller;

use app\api\OrderApi;
use app\common\controller\Base;

class Orders extends Base
{
    function index()
    {
        if(!session('id')) $this->redirect('/index/user?r=' . request()->url());
        $orderApi = new OrderApi();
        $orders = $orderApi->getOrders(1);
        var_export($orders);
        $this->assign('orders', $orders);
        return $this->fetch('index/orders/index');
    }
}   