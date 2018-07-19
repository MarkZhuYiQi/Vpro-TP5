<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/12
 * Time: 22:31
 */
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\VproOrder;
use app\common\model\VproOrderSub;
use think\Request;
use Yansongda\Pay\Exceptions\InvalidSignException;
use \Yansongda\Pay\Pay;
use \Yansongda\Pay\Log;

class PayOrders extends Base{
    protected $partner = 2088102172130805;
    protected $config = [
        'mode'                                  =>          'dev',
        'app_id'                                =>          '2016082000290082',
        'notify_url'                            =>          'http://223.112.88.210:8081/index/pay_orders/notify',
        'return_url'                            =>          'http://223.112.88.210:8081/index/pay_orders/res',
//        'ali_public_key'                        =>          'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA55OtHoprliJ1yerpF5SW3UkUATs5SP8Tgp3a88zwYxvdxtNA/smGLMBC79AgIYAKxK5FR4PfCaTbQKCjuO1DZJucJYxFjqrGmA59qW38LPJ2xhTyfXlcBR6xaooGnKMLhbqz4QyxpwggMZkwvHprYEx9lD1TMQWjpyRgr2SKuA+AbhD3BuM6AmdmDHzXEGTnqJJaggM+C4YX0jqADLgOHF5K2Vk+pOnC/6AFmDNYsVkO4eZPeENQ4YrWIxOEB5dkJFNgMX/8zLl9oUPGoSYfBpO/MzKZlKHM7tFlBD3urwMuAHsKkH5/I1l7FI2AqwQYesflGBaCqmyETh5tOztEKQIDAQAB',
//        'private_key'                           =>  'MIIEpQIBAAKCAQEA55OtHoprliJ1yerpF5SW3UkUATs5SP8Tgp3a88zwYxvdxtNA/smGLMBC79AgIYAKxK5FR4PfCaTbQKCjuO1DZJucJYxFjqrGmA59qW38LPJ2xhTyfXlcBR6xaooGnKMLhbqz4QyxpwggMZkwvHprYEx9lD1TMQWjpyRgr2SKuA+AbhD3BuM6AmdmDHzXEGTnqJJaggM+C4YX0jqADLgOHF5K2Vk+pOnC/6AFmDNYsVkO4eZPeENQ4YrWIxOEB5dkJFNgMX/8zLl9oUPGoSYfBpO/MzKZlKHM7tFlBD3urwMuAHsKkH5/I1l7FI2AqwQYesflGBaCqmyETh5tOztEKQIDAQABAoIBAQCNzO2a3+OVITDDHWbxm3jts0venScsvZRyzLo/w2QHLA8XKlCIM1pHmMrkEas7GC5/1L5zVhqCy0G+Rx85o3864dYxX71P6N6GSYlE8CYUV7vG+xipIGDqearls/LsgyIRFwwCaEV4JA+ij006fDO32d6joRGJ2Qwm0q2peIVAwoLz9nyr2zfIio+vX3VAXahcXkCPPOHV1b6zPL4tU0lnBuCdO6q6QU1yMRKAG/GVHT8ldekMqkzAGSoygE2c34Gp27emUvoRfESnCisnOwkimMaXnBs4WEhtaDULOy4CwFCkH8oeMb1MyyVEv0pHqXmLifWXKoJEpbD/G7Rs5rGpAoGBAPo2vyv3M4uf5j22YZ4JGzkx++Xv+V5E1Ni95FMmbLAuTelCYq/p+k92I78OC2AWjpVIvvbPdZ4Fe8SQukBJB6LxfluBC2q/XOq4+Ky1u+1miPxjE6RITrVMt4r1zkVJjEArJZW8WDikb0Wf8IB614F6vp+NdQ0VS9AbXXyt/rofAoGBAOzumXQvrDAJ3miDH7j44KyTJnE33WQaZBraCJ630YfxGUa07l3nX4YxeUkfQyYVAywZMQ1ko4kSpzZYCft8PEw09DdQm/dj6dKdfB5Jbmbeboz4MOd3CxdKa5fEQbKR9tmYcoTNcH64Hw3kWVXupd1h7EBkhTPoL6I3ZK+HxMi3AoGAOdZf2Fza+GJsyUUYSXyXY2AvdxZCkUzd2oACgEn4g70gW1PyFfHC341Sc/5eGMb+DHn1Un3gFTf1RRmjQ+rdrgeeiq5IolM7ujIpoVqc5yJ1dcm9J5NjRjtGjgOFu7RljAutM3CHAAjag8CVyk0a9Z4W5DDBptWOYbuBn6lkoUcCgYEAgWWNyTaAA3xgSxPRr2O80INM62hnMNR493E8Y/JgLK4v773AsOg78z3xz02TjqjLIrpfX8EmzyWwzK0oRoCDLdt9xPfxNhsLCEuaDbBs6yFvnu2tR7xsAjxSpoA4oR22gwAPCxhn580GqL+dSqEbVNy1+jTryn10BlPaWUL85eECgYEA3vS5CtLmR1ZhpXq96DkeFL472ZiZvOdueI7Zth3f1IoB4VnbkNYosfpHqXgIyDUmr1TVpuwt83EI9GEZMVRMZfNA9I0dAh06LmhDXvq0qcL+6EJBmdrtQPxccgDADMCn+6/n5sHgkMx7eFmN9vA38fHyhXDZkN0yI/zfrg4lafY=',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuWJKrQ6SWvS6niI+4vEVZiYfjkCfLQfoFI2nCp9ZLDS42QtiL4Ccyx8scgc3nhVwmVRte8f57TFvGhvJD0upT4O5O/lRxmTjechXAorirVdAODpOu0mFfQV9y/T9o9hHnU+VmO5spoVb3umqpq6D/Pt8p25Yk852/w01VTIczrXC4QlrbOEe3sr1E9auoC7rgYjjCO6lZUIDjX/oBmNXZxhRDrYx4Yf5X7y8FRBFvygIE2FgxV4Yw+SL3QAa2m5MLcbusJpxOml9YVQfP8iSurx41PvvXUMo49JG3BDVernaCYXQCoUJv9fJwbnfZd7J5YByC+5KM4sblJTq7bXZWQIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIEpAIBAAKCAQEAs6+F2leOgOrvj9jTeDhb5q46GewOjqLBlGSs/bVL4Z3fMr3p+Q1Tux/6uogeVi/eHd84xvQdfpZ87A1SfoWnEGH5z15yorccxSOwWUI+q8gz51IWqjgZxhWKe31BxNZ+prnQpyeMBtE25fXp5nQZ/pftgePyUUvUZRcAUisswntobDQKbwx28VCXw5XB2A+lvYEvxmMv/QexYjwKK4M54j435TuC3UctZbnuynSPpOmCu45ZhEYXd4YMsGMdZE5/077ZU1aU7wx/gk07PiHImEOCDkzqsFo0Buc/knGcdOiUDvm2hn2y1XvwjyFOThsqCsQYi4JmwZdRa8kvOf57nwIDAQABAoIBAQCw5QCqln4VTrTvcW+msB1ReX57nJgsNfDLbV2dG8mLYQemBa9833DqDK6iynTLNq69y88ylose33o2TVtEccGp8Dqluv6yUAED14G6LexS43KtrXPgugAtsXE253ZDGUNwUggnN1i0MW2RcMqHdQ9ORDWvJUCeZj/AEafgPN8AyiLrZeL07jJz/uaRfAuNqkImCVIarKUX3HBCjl9TpuoMjcMhz/MsOmQ0agtCatO1eoH1sqv5Odvxb1i59c8Hvq/mGEXyRuoiDo05SE6IyXYXr84/Nf2xvVNHNQA6kTckj8shSi+HGM4mO1Y4Pbb7XcnxNkT0Inn6oJMSiy56P+CpAoGBAO1O+5FE1ZuVGuLb48cY+0lHCD+nhSBd66B5FrxgPYCkFOQWR7pWyfNDBlmO3SSooQ8TQXA25blrkDxzOAEGX57EPiipXr/hy5e+WNoukpy09rsO1TMsvC+v0FXLvZ+TIAkqfnYBgaT56ku7yZ8aFGMwdCPL7WJYAwUIcZX8wZ3dAoGBAMHWplAqhe4bfkGOEEpfs6VvEQxCqYMYVyR65K0rI1LiDZn6Ij8fdVtwMjGKFSZZTspmsqnbbuCE/VTyDzF4NpAxdm3cBtZACv1Lpu2Om+aTzhK2PI6WTDVTKAJBYegXaahBCqVbSxieR62IWtmOMjggTtAKWZ1P5LQcRwdkaB2rAoGAWnAPT318Kp7YcDx8whOzMGnxqtCc24jvk2iSUZgb2Dqv+3zCOTF6JUsV0Guxu5bISoZ8GdfSFKf5gBAo97sGFeuUBMsHYPkcLehM1FmLZk1Q+ljcx3P1A/ds3kWXLolTXCrlpvNMBSN5NwOKAyhdPK/qkvnUrfX8sJ5XK2H4J8ECgYAGIZ0HIiE0Y+g9eJnpUFelXvsCEUW9YNK4065SD/BBGedmPHRC3OLgbo8X5A9BNEf6vP7fwpIiRfKhcjqqzOuk6fueA/yvYD04v+Da2MzzoS8+hkcqF3T3pta4I4tORRdRfCUzD80zTSZlRc/h286Y2eTETd+By1onnFFe2X01mwKBgQDaxo4PBcLL2OyVT5DoXiIdTCJ8KNZL9+kV1aiBuOWxnRgkDjPngslzNa1bK+klGgJNYDbQqohKNn1HeFX3mYNfCUpuSnD2Yag53Dd/1DLO+NxzwvTu4D6DCUnMMMBVaF42ig31Bs0jI3JQZVqeeFzSET8fkoFopJf3G6UXlrIEAQ==',
    ];

    /**
     * 异步通知结果
     */
    public function notify()
    {
        file_put_contents('./alipay_notify', '');
        $data = Request::instance()->param();
        $pay = Pay::alipay($this->config);
        file_put_contents('./alipay_notify', var_export($data, true), FILE_APPEND);
        try {
            $res = $pay->verify();
            if ($data['trade_status'] === 'TRADE_SUCCESS')
            {
                $vproOrder = new VproOrder();
                $vproOrderSub = new VproOrderSub();
                $orderMain = $vproOrder::get($data['out_trade_no']);
                // 商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号
                if (!$orderMain) throw new \Exception('order unknown', 403);
                $orderInfoMain = $orderMain->toArray();
                if (
                    // 判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）
                    floatval($orderInfoMain['order_price']) !== floatval($data['total_amount'])
                    || (string)$data['auth_app_id'] !== (string)$this->config['app_id']
                    // 校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
                    || (string)$data['seller_id'] !== $this->partner
                )
                {
                    throw new \Exception('payOrder Error!', 402);
                }
                $orderMain->order_payment = 1;
                $orderMain->order_payment_id = $data['trade_no'];
                $orderMain->order_payment_price = $data['total_amount'];
                $orderMain->save();
            } else {
                throw new \Exception('pay failed', 404);
            }
//            file_put_contents('./alipay_notify', var_export($data, true), FILE_APPEND);
        } catch (\Exception $e) {
            $res = $e->getMessage();
            $err = [
                'msg'       =>  $e->getMessage(),
                'file'      =>  $e->getFile(),
                'line'      =>  $e->getLine(),
                'trace'     =>  $e->getTraceAsString()
            ];
            file_put_contents('./alipay_notify', var_export($err, true), FILE_APPEND);
            // 日志记录
            return false;
//            file_put_contents('./alipay_notify', var_export($data, true), FILE_APPEND);
        }
        return $pay->success()->send();
    }

    /**
     * 同步通知结果, 这里可以作为一个用户的通知，而不去验证是否正确，验证交给异步通知去做。
     */
    public function res()
    {
        var_export(Request::instance()->param());
        var_export(Pay::alipay($this->config)->verify());
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
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->web($order);
        return $alipay->send();
    }
    public function pcpay($orderInfo)
    {
        $alipay = Pay::alipay($this->config)->web($orderInfo);
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