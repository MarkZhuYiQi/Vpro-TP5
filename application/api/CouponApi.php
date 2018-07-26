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
 *  coupon[id]                  优惠券条目
 *  couponExpired               优惠券是否过期条目
 *  userCoupon[userid]          优惠券 - 用户对应条目， 这里保存了所有用户领取过的优惠券
 *  userCouponIsExisted[userid] 优惠券 - 用户对应还可以使用的条目
 *  validUserCoupon[userid]     用户有效优惠券有效的条目存放地址，其实就是userCoupon与userCouponIsExisted交集，得到可以使用的优惠券
 *  用户优惠券领取条目：
 * Class CouponApi
 * @package app\api
 */
class CouponApi extends BaseApi
{
    /**
     * 获得用户所拥有的优惠券信息
     * 如果redis存在优惠券信息，那么就
     * @param int $authId
     * @return array|bool|int
     */
    function getCoupon(int $authId)
    {
        $this->authId = $authId;
        if (!$this->redis->exists($this->userCouponPrefix . $authId)) {
            // redis没有优惠券信息，查询数据库
            $coupon = Db::table('vpro_user_coupon')->where('user_coupon_auth_id', $authId)->find();
            // 数据库里也没有
            if (!$coupon) {
                $coupon = [
                    'user_coupon_auth_id'       =>  $authId,
                    'user_coupon_bit'           =>  '64',
                    'user_coupon_isexisted_bit' =>  '64',
                ];
                Db::table('vpro_user_coupon')->insert($coupon);
            }
            $this->setCoupon($coupon);
        }
        if (!$this->redis->exists($this->couponExpiredPrefix))
        {
            $expired = Db::table('vpro_coupon_expired')->where('vpro_expired_id', 1)->find();
            $this->setExpired($expired);
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
     * @param $type 1是可使用，2是不可使用，3是已过期
     * @return array|bool
     */
    function validateCoupon($coupon, $type=1)
    {
        if (is_array($coupon))
        {
            $expiredCoupons = [];
            $validCoupons = array_filter($coupon, function($c) use ($expiredCoupons) {
                $validate = $this->checkCouponIfIllegal($c);
                $notExpired = $this->checkCouponIfExpired($c);
                if ($notExpired) $expiredCoupons[] = $c;
                if (!$validate) {
                    $this->modifyCouponIsExistedStatus($c['coupon_id'], 0);
                }
                return !!($validate && $notExpired);
            });
            if($type === 1)
            {
                return $validCoupons;
            }
            elseif ($type === 2)
            {

            }
            elseif ($type === 3)
            {
                return $expiredCoupons;
            }
        } else {
            $notExpired = $this->checkCouponIfExpired($coupon);
            if (!$this->checkCouponIfIllegal($coupon))
            {
                $this->modifyCouponIsExistedStatus($coupon['coupon_id'], 0);
                return false;
            }
            return $notExpired ? $coupon : false;
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
    private function checkCouponIfIllegal(array $coupon)
    {
        // 这里的优惠券时间应该放到过期的信息里而不是这里。
        if ($coupon['coupon_limit'] > $coupon['coupon_discount'])
        {
            return $coupon;
        }
        return false;
    }

    /**
     * 检查是否过期，如果过期了就去redis设置一下
     * @param array $coupon
     * @return array|bool 过期返回false 否则返回信息
     */
    private function checkCouponIfExpired(array $coupon)
    {
        if ($coupon['coupon_end_date'] < time())
        {
            $this->redis->setBit($this->couponExpiredPrefix, $coupon['coupon_id'], 1);
            return false;
        }
        return $coupon;
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

    /**
     * 设置优惠券状态，如果被使用，修改用户优惠券是否可用为不可用
     * @param int $couponId
     * @param int $status
     */
    function modifyUserCouponStatus(int $couponId, int $status)
    {
        $this->redis->multi()
            ->setBit($this->userCouponPrefix . $this->authId, $couponId, $status)
            ->setBit($this->userCouponIsExistedPrefix . $this->authId, $couponId, $status)
            ->exec();
    }

    /**
     * 修改该用户优惠券是否可用状态
     * @param int $couponId
     * @param int $status
     */
    function modifyCouponIsExistedStatus(int $couponId, int $status)
    {
        $this->redis->setBit($this->userCouponIsExistedPrefix . $this->authId, $couponId, $status);
    }
    /**
     * 设置Coupon bit，将数据库读出的用户优惠券数据，ascii码转换成字符然后放入redis中
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
     * 设置过期时间的bit
     * @param array $expired
     */
    function setExpired(array $expired)
    {
        $strExpired = $this->AsciiNum2Str($expired['vpro_expired_bit']);
        $this->redis->set($this->couponExpiredPrefix, $strExpired);
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
     * 将优惠券认领bit和优惠券是否有效bit还有优惠券是否过期bit进行AND，获得最终序列，返回redis。get到的是字符串结果（等待转换成ascii）
     * 关于优惠券过期：
     *  过期优惠券条目设置：过期的取1，没过期的取0
     *  先将过期优惠券和有效优惠券AND，结果是有效优惠券中过期的条目。
     *  然后将其取反，得到没过期的有效条目（但是bit操作是8位8位的，如果取反，在8位以内都取反，即使那一位之前并未设置）
     *  再次和有效优惠券and，得到的就是未过期的有效优惠券
     * @param $authId
     * @return string
     */
    function genValidCoupon(int $authId):string
    {
        // 取得有效的优惠券条目存入validUserCoupon1
        $this->redis->bitOp('AND', $this->validUserCouponPrefix . $authId, $this->userCouponPrefix . $authId, $this->userCouponIsExistedPrefix . $authId);
        //
        $this->redis->bitOp('AND', $this->expiredStr . $authId, $this->validUserCouponPrefix . $authId, $this->couponExpiredPrefix);
        $this->redis->bitOp('NOT', $this->expiredStr . $authId, $this->expiredStr . $authId);
        $this->redis->bitOp('AND', $this->validUserCouponPrefixWithExpired . $authId, $this->expiredStr . $authId, $this->validUserCouponPrefix . $authId);
        $this->redis->del($this->expiredStr . $authId);
        return $this->redis->get($this->validUserCouponPrefixWithExpired . $authId);
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