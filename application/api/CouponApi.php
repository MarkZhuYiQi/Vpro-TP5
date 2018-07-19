<?php
namespace app\api;
use app\common\controller\Redis;
use think\Db;
/**
 * 优惠券处理类
 *
 * 优惠券的领取情况以用户为单位，每个用户单独维护一个是否领取的字符串。
 * 该字符串是由一个2^32次方个bit（维护42亿的bit）的0 1数字组成
 * 存入数据库的方法是将数据转换成ascii码然后转换成字符串存入，取出就反操作。
 * 第二份信息是客户优惠券是否可用，如果用户已经使用过，优惠券已经失效，就修改更改existed优惠券条目
 * 最后取交集，就是可用列表.
 *
 * redis缓存采取以下命名规则：
 *  优惠券条目：coupon[id]
 *  优惠券用户对应条目： userCoupon[userid]
 *  优惠券用户对应是否可以使用条目： userCouponIsExisted[userid]
 *  用户优惠券领取条目：
 * Class CouponApi
 * @package app\api
 */
class CouponApi extends BaseApi
{
    /**
     * 获得用户所拥有的优惠券信息
     * @param int $authId
     * @return array|bool|int
     */
    function getCoupon(int $authId)
    {
        $this->authId = $authId;
        if ($this->redis->exists($this->userCouponPrefix . $authId)) {
            $coupon = Db::table('vpro_user_coupon')->where('user_coupon_auth_id', $authId)->find();
//            var_export($coupon);exit();
            if (!$coupon) return 0;
            $this->setCoupon($coupon);
        }
        $validCouponStr = $this->genValidCoupon($authId);
        $validCouponNum = $this->Str2AsciiNum($validCouponStr);
        // 无可用优惠券
        if (!$validCouponNum) return false;
        $couponIds = $this->getCouponIds($validCouponNum);
        return $this->getCouponsInfo($couponIds);
    }
    function checkUserHasCoupon($couponId, $authId)
    {
        $this->genValidCoupon($authId);
        return $this->redis->getBit($couponId);
    }
    /**
     * 验证优惠券（或者优惠券集合），判断是否符合逻辑
     * @param $coupon
     * @return array|bool
     */
    function validateCoupon($coupon)
    {
        if (is_array($coupon))
        {
            return array_filter($coupon, function($c) {
                $validate = $this->checkCouponInfo($c);
                if (!$validate) {
                    $this->modifyCouponIsExistedStatus($c['coupon_id'], 0);
                }
                return !!($validate);
            });
        } else {
            if (!$this->checkCouponInfo($coupon))
            {
                $this->modifyCouponIsExistedStatus($coupon['coupon_id'], 0);
                return false;
            }
            return $coupon;
        }
    }

    /**
     * 优惠券是否符合当前订单要求
     * @param $sumPrice
     * @param $coupons
     * @return mixed
     */
    function checkCouponForOrder($sumPrice, $coupons)
    {
        if (is_array($coupons))
        {
            foreach($coupons as $coupon)
            {
                if ($coupon['coupon_limit'] >= $sumPrice || time() < $coupon['coupon_start_date'] || time() > $coupon['coupon_end_date'])
                {
                    unset($coupon);
                }
            }
        } else {
            if ($coupons['coupon_limit'] >= $sumPrice || time() < $coupons['coupon_start_date'] || time() > $coupons['coupon_end_date'])
            {
                return false;
            }
        }
        return $coupons;
    }

    /**
     * 内部方法
     * 检查优惠券信息是否合法，
     * @param array $coupon
     * @return array|bool
     */
    function checkCouponInfo(array $coupon)
    {
        // 这里的优惠券时间应该放到过期的信息里而不是这里。
        if ($coupon['coupon_limit'] > $coupon['coupon_discount'])
        {
            return $coupon;
        }
        return false;
    }
    /**
     * 获得优惠券信息
     * @param $couponIds
     * @return array
     */
    function getCouponsInfo(array $couponIds):array
    {
        $coupons= [];
        foreach($couponIds as $id)
        {
            $res = $this->genCouponInfo($id);
            if ($res) array_push($coupons, $res);
        }
        return $coupons;
    }

    /**
     * 生成优惠券详细信息，redis没有就去数据库取，没有找到说明提供的id有误
     * @param $couponId
     * @return bool
     */
    function genCouponInfo(int $couponId)
    {
        // 优惠券存储key
        $couponKey = $this->couponPrefix . $couponId;
        if (!$this->redis->exists($couponKey))
        {
            // 优惠券表中寻找这张优惠券的信息
            $res = Db::table('vpro_coupon')->where('coupon_id', $couponId)->find();
            if ($res)
            {
                $this->redis->hMset($couponKey, $res);
                return $res;
            }
            // 没有找到，修改coupon相关信息。
            $this->modifyUserCouponStatus($couponId, 0);
            Redis::scorePoint(config('key.DB_MISS'));
            return false;
        }
        return $this->redis->hGetAll($couponKey);
    }
    function modifyUserCouponStatus(int $couponId, int $status)
    {
        $this->redis->multi()
            ->setBit($this->userCouponPrefix . $this->authId, $couponId, $status)
            ->setBit($this->userCouponIsExistedPrefix . $this->authId, $couponId, $status)
            ->exec();
    }
    function modifyCouponIsExistedStatus(int $couponId, int $status)
    {
        $this->redis->setBit($this->userCouponIsExistedPrefix . $this->authId, $couponId, $status);
    }
    /**
     * 设置Coupon bit，将ascii码转换成字符然后放入redis中
     * @param $coupon
     * @param $authId
     */
    function setCoupon(array $coupon)
    {
        $strUserCoupon = $this->AsciiNum2Str($coupon['user_coupon_bit']);
        $strIsExisted = $this->AsciiNum2Str($coupon['user_coupon_isexisted_bit']);
        $this->redis->set($this->userCouponPrefix . $this->authId, $strUserCoupon);
        $this->redis->set($this->userCouponIsExistedPrefix . $this->authId, $strIsExisted);
    }

    /**
     * 将十进制字符串分割成数组，根据位置，计算出每一位值为1的bit对应的位置，这个位置就是优惠券id
     * @param $couponStr , 十进制，由8个字节bit组成，逗号隔开
     * @return array 返回包含所有有效优惠券id的数组
     */
    function getCouponIds(string $couponNum):array
    {
        $couponIds = [];
        foreach (explode(',', $couponNum) as $key => $value) {
            $value = decbin($value);
            if (strlen($value) < 8) $value = sprintf('%08s', $value);
            foreach (str_split($value) as $k => $v) {
                if ($v == 1) array_push($couponIds, $key * 8 + $k);
            }
        }
        return $couponIds;
    }

    /**
     * 生成有效的优惠券bit
     * 将优惠券认领bit和优惠券是否有效bit进行AND，获得最终序列，返回redis。get到的是字符串结果（等待转换成ascii）
     * @param $authId
     * @return mixed
     */
    function genValidCoupon(int $authId):string
    {
        $this->redis->bitOp('AND', $this->validUserCouponPrefix . $authId, $this->userCouponPrefix . $authId, $this->userCouponIsExistedPrefix . $authId);
        return $this->redis->get($this->validUserCouponPrefix . $authId);
    }
    function useCoupon($couponId, $authId)
    {
        $this->redis->multi()
            ->setBit($this->userCouponIsExistedPrefix . $authId, $couponId, 0)
            ->setBit($this->userCouponUsedPrefix . $authId, $couponId, 1)
            ->exec();
    }
    /**
     * 将ascii码转换成对应的字符串，用于数据库的数据转换成redis的数据
     * 将提供的十进制数字，转换成ascii码对应的字符串
     * @param $AsciiNum
     * @return string
     */
    private function AsciiNum2Str(int $AsciiNum):string
    {
        $AsciiNum = explode(',', $AsciiNum);
        $res = '';
        foreach($AsciiNum as $ascii)
        {
            $res .= chr($ascii);
        }
        return $res;
    }

    /**
     * 用于将redis取出的字符串转换成十进制的字符串对应ascii码存入数据库
     * 将字符串中每个字符单独分离出来找出其对应的ascii码并连接起来，用逗号隔开
     * @param $str
     * @return string
     */
    private function Str2AsciiNum(string $str):string
    {
        $strArr = str_split($str);
        $res = [];
        foreach($strArr as $s)
        {
            $res[] = ord($s);
        }
        return implode(',', $res);
    }
}