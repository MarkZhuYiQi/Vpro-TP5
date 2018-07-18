<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/12
 * Time: 22:31
 */
namespace app\index\controller;

use app\common\controller\Base;
use think\Request;
use Yansongda\Pay\Exceptions\InvalidSignException;
use \Yansongda\Pay\Pay;
use \Yansongda\Pay\Log;

class PayOrders extends Base{
    protected $config = [
        'mode'                                  =>          'dev',
        'app_id'                                =>          2016082000290082,
        'notify_url'                            =>          'http://223.112.88.210:8081/index/pay_orders/notify',
        'return_url'                            =>          'http://223.112.88.210:8081/index/pay_orders/res',
        'ali_public_key'                        =>          'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA55OtHoprliJ1yerpF5SW3UkUATs5SP8Tgp3a88zwYxvdxtNA/smGLMBC79AgIYAKxK5FR4PfCaTbQKCjuO1DZJucJYxFjqrGmA59qW38LPJ2xhTyfXlcBR6xaooGnKMLhbqz4QyxpwggMZkwvHprYEx9lD1TMQWjpyRgr2SKuA+AbhD3BuM6AmdmDHzXEGTnqJJaggM+C4YX0jqADLgOHF5K2Vk+pOnC/6AFmDNYsVkO4eZPeENQ4YrWIxOEB5dkJFNgMX/8zLl9oUPGoSYfBpO/MzKZlKHM7tFlBD3urwMuAHsKkH5/I1l7FI2AqwQYesflGBaCqmyETh5tOztEKQIDAQAB',
        'private_key'                           =>  'MIIEpQIBAAKCAQEA55OtHoprliJ1yerpF5SW3UkUATs5SP8Tgp3a88zwYxvdxtNA/smGLMBC79AgIYAKxK5FR4PfCaTbQKCjuO1DZJucJYxFjqrGmA59qW38LPJ2xhTyfXlcBR6xaooGnKMLhbqz4QyxpwggMZkwvHprYEx9lD1TMQWjpyRgr2SKuA+AbhD3BuM6AmdmDHzXEGTnqJJaggM+C4YX0jqADLgOHF5K2Vk+pOnC/6AFmDNYsVkO4eZPeENQ4YrWIxOEB5dkJFNgMX/8zLl9oUPGoSYfBpO/MzKZlKHM7tFlBD3urwMuAHsKkH5/I1l7FI2AqwQYesflGBaCqmyETh5tOztEKQIDAQABAoIBAQCNzO2a3+OVITDDHWbxm3jts0venScsvZRyzLo/w2QHLA8XKlCIM1pHmMrkEas7GC5/1L5zVhqCy0G+Rx85o3864dYxX71P6N6GSYlE8CYUV7vG+xipIGDqearls/LsgyIRFwwCaEV4JA+ij006fDO32d6joRGJ2Qwm0q2peIVAwoLz9nyr2zfIio+vX3VAXahcXkCPPOHV1b6zPL4tU0lnBuCdO6q6QU1yMRKAG/GVHT8ldekMqkzAGSoygE2c34Gp27emUvoRfESnCisnOwkimMaXnBs4WEhtaDULOy4CwFCkH8oeMb1MyyVEv0pHqXmLifWXKoJEpbD/G7Rs5rGpAoGBAPo2vyv3M4uf5j22YZ4JGzkx++Xv+V5E1Ni95FMmbLAuTelCYq/p+k92I78OC2AWjpVIvvbPdZ4Fe8SQukBJB6LxfluBC2q/XOq4+Ky1u+1miPxjE6RITrVMt4r1zkVJjEArJZW8WDikb0Wf8IB614F6vp+NdQ0VS9AbXXyt/rofAoGBAOzumXQvrDAJ3miDH7j44KyTJnE33WQaZBraCJ630YfxGUa07l3nX4YxeUkfQyYVAywZMQ1ko4kSpzZYCft8PEw09DdQm/dj6dKdfB5Jbmbeboz4MOd3CxdKa5fEQbKR9tmYcoTNcH64Hw3kWVXupd1h7EBkhTPoL6I3ZK+HxMi3AoGAOdZf2Fza+GJsyUUYSXyXY2AvdxZCkUzd2oACgEn4g70gW1PyFfHC341Sc/5eGMb+DHn1Un3gFTf1RRmjQ+rdrgeeiq5IolM7ujIpoVqc5yJ1dcm9J5NjRjtGjgOFu7RljAutM3CHAAjag8CVyk0a9Z4W5DDBptWOYbuBn6lkoUcCgYEAgWWNyTaAA3xgSxPRr2O80INM62hnMNR493E8Y/JgLK4v773AsOg78z3xz02TjqjLIrpfX8EmzyWwzK0oRoCDLdt9xPfxNhsLCEuaDbBs6yFvnu2tR7xsAjxSpoA4oR22gwAPCxhn580GqL+dSqEbVNy1+jTryn10BlPaWUL85eECgYEA3vS5CtLmR1ZhpXq96DkeFL472ZiZvOdueI7Zth3f1IoB4VnbkNYosfpHqXgIyDUmr1TVpuwt83EI9GEZMVRMZfNA9I0dAh06LmhDXvq0qcL+6EJBmdrtQPxccgDADMCn+6/n5sHgkMx7eFmN9vA38fHyhXDZkN0yI/zfrg4lafY=',
    ];

    /**
     * 异步通知结果
     */
    public function notify()
    {
        file_put_contents('./alipay_notify', '');
        $data = Request::instance()->param();
        file_put_contents('./alipay_notify', $data, FILE_APPEND);
        try {
            $data = Pay::alipay($this->config)->verify();
            file_put_contents('./alipay_notify', $data, FILE_APPEND);
        } catch (InvalidSignException $e) {
            $err = $e->getMessage();
            file_put_contents('./alipay_notify', $data, FILE_APPEND);
        }
    }

    /**
     * 同步通知结果, 这里可以作为一个用户的通知，而不去验证是否正确，验证交给异步通知去做。
     */
    public function res()
    {
        var_export(Request::instance()->param());
    }
    public function test()
    {
        var_export(getcwd());
    }
    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试1',
        ];
        $alipay = Pay::alipay($this->config)->web($order);
        $alipay->send();
    }
    public function mobile()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject'      => 'test subject-测试订单',
        ];
        $alipay = Pay::alipay($this->config);
        return $alipay->wap($order)->send(); // laravel 框架中请直接 return $alipay->wap($order)
    }
    public function app()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject'      => 'test subject-测试订单',
        ];

// 将返回字符串，供后续 APP 调用，调用方式不在本文档讨论范围内，请参考官方文档。
        return Pay::alipay($this->config)->app($order)->send(); // laravel 框架中请直接 return $alipay->app($order)
    }
    public function postcard()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject'      => 'test subject-刷卡支付',
            'auth_code' => '289756915257123456',
        ];

        $result = Pay::alipay($this->config)->pos($order);
    }
    public function qrcode()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject'      => 'test subject-刷卡支付',
        ];

        $result = Pay::alipay($this->config)->scan($order);
    }
    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }
}