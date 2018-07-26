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
use think\Request;

class Orders extends Base
{
    function index()
    {
        if(!session('id')) $this->redirect('/index/user?r=' . request()->url());
        $orderApi = new OrderApi();
        $orders = $orderApi->getOrders(1);
        $this->assign('orders', $orders);
        return $this->fetch('index/orders/index');
    }
    function getOrders()
    {
        $params = Request::instance()->route();
        $orderApi = new OrderApi();
        $orders = $orderApi->getOrders(1);
        echo json_encode($orders);
    }
}