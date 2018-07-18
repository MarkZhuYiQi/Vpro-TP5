<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/7/5
 * Time: 21:29
 */
namespace app\index\validate;

use think\Validate;

class User extends Validate{
    protected $rule = [
        'course_id' =>  'require|number'
    ];
}