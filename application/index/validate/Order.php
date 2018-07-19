<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/19/2018
 * Time: 10:18 AM
 */
namespace app\index\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'orderId'       =>  ['require', 'length' => 19],
        'orderPrice'    =>  ['require', 'float'],
        'authId'        =>  ['require', 'number'],
        'coupon'        =>  ['array'],
        'courses'       =>  ['require', 'array']
    ];
}