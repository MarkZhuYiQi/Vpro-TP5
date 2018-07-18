<?php
namespace app\common\controller;
use app\common\controller\RedisSession;
use think\Controller;

/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 5/31/2018
 * Time: 11:33 AM
 */

/**
 * 这个类必须继承，继承后开启session才能进行session操作。替代了contronller
 * Class Base
 * @package app\common\controller
 */
class Base extends controller
{
    protected $sessionHandler;
    public function __construct(\think\Request $request = null)
    {
        parent::__construct($request);
        // 这里完全初始化了sessionHandler，将session的处理方法更改为自定义方法，并且开启session
        // 这里遇到的坑，session在整个生命周期中只能初始化一次，开启以后不能再次调用，所以从头到尾必须只用到一个对象。
        // 在调用这里的API时，因为session已经start，所以不能另外再去继承Base初始化了，如果需要controller的方法，直接继承controller
//        $this->sessionHandler = new RedisSession();
//        $this->sessionHandler->begin();
//        if(!isset($_SESSION))session_start();
    }
}