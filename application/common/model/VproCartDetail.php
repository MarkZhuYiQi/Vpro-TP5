<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/9/2018
 * Time: 11:11 AM
 */
namespace app\common\model;
use think\Model;

class VproCartDetail extends Model
{
    protected $resultSetType = 'collection';
    protected $pk = 'cart_Detail_id';
    protected $table = 'vpro_cart_detail';
}