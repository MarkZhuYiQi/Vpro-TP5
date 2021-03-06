<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/19
 * Time: 22:19
 */
namespace app\index\controller;

use app\common\controller\Base;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;

class TestPay extends Base{
    protected $config = [
//        'app_id' => '2016082000295641',
        'app_id'                                =>          '2016082000290082',
        'notify_url' => 'http://223.112.88.210:8081/index/test_pay/notify',
        'return_url' => 'http://223.112.88.210:8081/index/test_pay/return',
//        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuWJKrQ6SWvS6niI+4vEVZiYfjkCfLQfoFI2nCp9ZLDS42QtiL4Ccyx8scgc3nhVwmVRte8f57TFvGhvJD0upT4O5O/lRxmTjechXAorirVdAODpOu0mFfQV9y/T9o9hHnU+VmO5spoVb3umqpq6D/Pt8p25Yk852/w01VTIczrXC4QlrbOEe3sr1E9auoC7rgYjjCO6lZUIDjX/oBmNXZxhRDrYx4Yf5X7y8FRBFvygIE2FgxV4Yw+SL3QAa2m5MLcbusJpxOml9YVQfP8iSurx41PvvXUMo49JG3BDVernaCYXQCoUJv9fJwbnfZd7J5YByC+5KM4sblJTq7bXZWQIDAQAB',
        // 加密方式： **RSA2**
//        'private_key' => 'MIIEpAIBAAKCAQEAs6+F2leOgOrvj9jTeDhb5q46GewOjqLBlGSs/bVL4Z3fMr3p+Q1Tux/6uogeVi/eHd84xvQdfpZ87A1SfoWnEGH5z15yorccxSOwWUI+q8gz51IWqjgZxhWKe31BxNZ+prnQpyeMBtE25fXp5nQZ/pftgePyUUvUZRcAUisswntobDQKbwx28VCXw5XB2A+lvYEvxmMv/QexYjwKK4M54j435TuC3UctZbnuynSPpOmCu45ZhEYXd4YMsGMdZE5/077ZU1aU7wx/gk07PiHImEOCDkzqsFo0Buc/knGcdOiUDvm2hn2y1XvwjyFOThsqCsQYi4JmwZdRa8kvOf57nwIDAQABAoIBAQCw5QCqln4VTrTvcW+msB1ReX57nJgsNfDLbV2dG8mLYQemBa9833DqDK6iynTLNq69y88ylose33o2TVtEccGp8Dqluv6yUAED14G6LexS43KtrXPgugAtsXE253ZDGUNwUggnN1i0MW2RcMqHdQ9ORDWvJUCeZj/AEafgPN8AyiLrZeL07jJz/uaRfAuNqkImCVIarKUX3HBCjl9TpuoMjcMhz/MsOmQ0agtCatO1eoH1sqv5Odvxb1i59c8Hvq/mGEXyRuoiDo05SE6IyXYXr84/Nf2xvVNHNQA6kTckj8shSi+HGM4mO1Y4Pbb7XcnxNkT0Inn6oJMSiy56P+CpAoGBAO1O+5FE1ZuVGuLb48cY+0lHCD+nhSBd66B5FrxgPYCkFOQWR7pWyfNDBlmO3SSooQ8TQXA25blrkDxzOAEGX57EPiipXr/hy5e+WNoukpy09rsO1TMsvC+v0FXLvZ+TIAkqfnYBgaT56ku7yZ8aFGMwdCPL7WJYAwUIcZX8wZ3dAoGBAMHWplAqhe4bfkGOEEpfs6VvEQxCqYMYVyR65K0rI1LiDZn6Ij8fdVtwMjGKFSZZTspmsqnbbuCE/VTyDzF4NpAxdm3cBtZACv1Lpu2Om+aTzhK2PI6WTDVTKAJBYegXaahBCqVbSxieR62IWtmOMjggTtAKWZ1P5LQcRwdkaB2rAoGAWnAPT318Kp7YcDx8whOzMGnxqtCc24jvk2iSUZgb2Dqv+3zCOTF6JUsV0Guxu5bISoZ8GdfSFKf5gBAo97sGFeuUBMsHYPkcLehM1FmLZk1Q+ljcx3P1A/ds3kWXLolTXCrlpvNMBSN5NwOKAyhdPK/qkvnUrfX8sJ5XK2H4J8ECgYAGIZ0HIiE0Y+g9eJnpUFelXvsCEUW9YNK4065SD/BBGedmPHRC3OLgbo8X5A9BNEf6vP7fwpIiRfKhcjqqzOuk6fueA/yvYD04v+Da2MzzoS8+hkcqF3T3pta4I4tORRdRfCUzD80zTSZlRc/h286Y2eTETd+By1onnFFe2X01mwKBgQDaxo4PBcLL2OyVT5DoXiIdTCJ8KNZL9+kV1aiBuOWxnRgkDjPngslzNa1bK+klGgJNYDbQqohKNn1HeFX3mYNfCUpuSnD2Yag53Dd/1DLO+NxzwvTu4D6DCUnMMMBVaF42ig31Bs0jI3JQZVqeeFzSET8fkoFopJf3G6UXlrIEAQ==',
        'ali_public_key'                        =>          'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5R6gWkmNgDR2h1j19OeKWjUnjxvqa3bAEtJYTNSezMxGVxsbcpqJlJTqG70oWBTWu7P0rp0X9Zy2QIASzo5A67ictsJWqE90EYvNs5GLbe/xJGKMBL3ToiTNuO0weDagwRaQqQbKBsV+E8uS0AcZihHD2ybez1n4qSJVM4kWi/XI9Bqx/+MT7IcD344OTsdkF3g6PUp+ipGSqCPVl8kWyedwuRrI2WVMCrFVRyPkEEMoymSB1gf107Ss+sgzEFgk2PNgh7dHwnyeRLkTlbQQZUpF94ZqLX/BRZ5/ptugpNnKp6dbgu/YeXWl66HduGhEeLWnfotb6hgBKS23JWuV7QIDAQAB',
        'private_key'                           =>  'MIIEpQIBAAKCAQEA55OtHoprliJ1yerpF5SW3UkUATs5SP8Tgp3a88zwYxvdxtNA/smGLMBC79AgIYAKxK5FR4PfCaTbQKCjuO1DZJucJYxFjqrGmA59qW38LPJ2xhTyfXlcBR6xaooGnKMLhbqz4QyxpwggMZkwvHprYEx9lD1TMQWjpyRgr2SKuA+AbhD3BuM6AmdmDHzXEGTnqJJaggM+C4YX0jqADLgOHF5K2Vk+pOnC/6AFmDNYsVkO4eZPeENQ4YrWIxOEB5dkJFNgMX/8zLl9oUPGoSYfBpO/MzKZlKHM7tFlBD3urwMuAHsKkH5/I1l7FI2AqwQYesflGBaCqmyETh5tOztEKQIDAQABAoIBAQCNzO2a3+OVITDDHWbxm3jts0venScsvZRyzLo/w2QHLA8XKlCIM1pHmMrkEas7GC5/1L5zVhqCy0G+Rx85o3864dYxX71P6N6GSYlE8CYUV7vG+xipIGDqearls/LsgyIRFwwCaEV4JA+ij006fDO32d6joRGJ2Qwm0q2peIVAwoLz9nyr2zfIio+vX3VAXahcXkCPPOHV1b6zPL4tU0lnBuCdO6q6QU1yMRKAG/GVHT8ldekMqkzAGSoygE2c34Gp27emUvoRfESnCisnOwkimMaXnBs4WEhtaDULOy4CwFCkH8oeMb1MyyVEv0pHqXmLifWXKoJEpbD/G7Rs5rGpAoGBAPo2vyv3M4uf5j22YZ4JGzkx++Xv+V5E1Ni95FMmbLAuTelCYq/p+k92I78OC2AWjpVIvvbPdZ4Fe8SQukBJB6LxfluBC2q/XOq4+Ky1u+1miPxjE6RITrVMt4r1zkVJjEArJZW8WDikb0Wf8IB614F6vp+NdQ0VS9AbXXyt/rofAoGBAOzumXQvrDAJ3miDH7j44KyTJnE33WQaZBraCJ630YfxGUa07l3nX4YxeUkfQyYVAywZMQ1ko4kSpzZYCft8PEw09DdQm/dj6dKdfB5Jbmbeboz4MOd3CxdKa5fEQbKR9tmYcoTNcH64Hw3kWVXupd1h7EBkhTPoL6I3ZK+HxMi3AoGAOdZf2Fza+GJsyUUYSXyXY2AvdxZCkUzd2oACgEn4g70gW1PyFfHC341Sc/5eGMb+DHn1Un3gFTf1RRmjQ+rdrgeeiq5IolM7ujIpoVqc5yJ1dcm9J5NjRjtGjgOFu7RljAutM3CHAAjag8CVyk0a9Z4W5DDBptWOYbuBn6lkoUcCgYEAgWWNyTaAA3xgSxPRr2O80INM62hnMNR493E8Y/JgLK4v773AsOg78z3xz02TjqjLIrpfX8EmzyWwzK0oRoCDLdt9xPfxNhsLCEuaDbBs6yFvnu2tR7xsAjxSpoA4oR22gwAPCxhn580GqL+dSqEbVNy1+jTryn10BlPaWUL85eECgYEA3vS5CtLmR1ZhpXq96DkeFL472ZiZvOdueI7Zth3f1IoB4VnbkNYosfpHqXgIyDUmr1TVpuwt83EI9GEZMVRMZfNA9I0dAh06LmhDXvq0qcL+6EJBmdrtQPxccgDADMCn+6/n5sHgkMx7eFmN9vA38fHyhXDZkN0yI/zfrg4lafY=',
        'log' => [ // optional
//            'file' => './logs/alipay.log',
            'level' => 'debug'
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->web($order);

        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }

    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        var_export($data);
        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notify()
    {
        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！
            var_export($data);

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
}