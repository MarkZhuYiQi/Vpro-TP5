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
        var_export($orderApi->getOrdersCount(1));
        $this->assign('orders', $orders);
        return $this->fetch('index/orders/index');
    }
    function getOrders()
    {
        // 0 待付款 1 支付成功 2 交易关闭 3 所有订单
        $params = Request::instance()->route();
        $type = $params['type'];
        $page = $params['page'] ?? 1;
        $orderApi = new OrderApi();
        $orders = $orderApi->getOrders($type, $page);
        echo json_encode($orders);
    }
    function getOrderPageInfo()
    {
        $params = Request::instance()->route();
        $type = $params['type'];
        if (!in_array($type, ['0', '1', '2', '3'])) {
            echo 0;
            exit();
        }
        $orderApi = new OrderApi();
        $ordersCount = $orderApi->getOrdersCount($type);
        echo $ordersCount;
    }
}