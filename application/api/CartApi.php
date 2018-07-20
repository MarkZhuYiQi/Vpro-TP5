<?php
namespace app\api;
use app\common\controller\Redis;
use app\common\controller\ReturnFormat;
use app\common\controller\Snowflake;
use app\common\model\VproCart;
use app\common\model\VproCartDetail;
use think\Cookie;
use think\Db;


class CartApi extends BaseApi
{
    public function getCartItems($userId)
    {
        $query = <<<query
SELECT
	g.*,
	va.auth_appid
FROM
	(
		SELECT
			vc.course_id,
			vc.course_title,
			vc.course_pid,
			vc.course_author,
			vc.course_price,
			vcc.course_cover_address,
			cd.cart_add_time
		FROM
			(
				SELECT
					vcd.cart_course_id AS course_id,
					vcd.cart_add_time as cart_add_time
				FROM
					vpro_cart vc
				LEFT JOIN vpro_cart_detail vcd ON vc.cart_id = vcd.cart_parent_id
				WHERE
					vc.cart_userid = $userId
			) AS cd
		LEFT JOIN vpro_courses AS vc ON vc.course_id = cd.course_id
		LEFT JOIN vpro_courses_cover AS vcc ON vcc.course_cover_id = cd.course_id
	) g
LEFT JOIN vpro_auth as va ON va.auth_id = g.course_author
ORDER BY g.cart_add_time DESC
query;
        $res = Db::query($query);
        return $res;
    }

    public function combineCourseInfo($courseId)
    {
        $courseApi = new CourseApi();
        $course = $courseApi->getCourseInfo($courseId, 'course');
        $cover = $courseApi->getCourseInfo($courseId, 'cover');
        $author = $courseApi->getCourseInfo($course['course_author'], 'auth');
        if (!$course || !$cover || !$author)
        {
            return false;
        }
        $cartInfo = [
            'course_id'                 =>  $courseId,
            'course_title'              =>  $course['course_title'],
            'course_pid'                =>  $course['course_pid'],
            'course_cover_address'      =>  $cover['course_cover_address'],
            'cart_add_time'             =>  time(),
            'course_price'              =>  $course['course_price'],
            'course_author'             =>  $author['auth_appid'],
            'course_discount_price'     =>  $course['course_discount_price']
        ];
        return $cartInfo;
    }

    /**
     * 未登录状态，添加商品到cookie的购物车中
     * @param $courseId
     */
    public function addToCookieCart($courseId)
    {
        $courseInfo = $this->combineCourseInfo($courseId);
        if (!$courseInfo) return ReturnFormat::json('课程有误，不要乱来~', 'CART_COURSE_INFO_ERROR');
        if ($cart = Cookie::get('cart'))
        {
            $id = $cart[0]['cart_id'];
        }
        else
        {
            $cart = [];
            $id = new Snowflake();
            $id = $id->getOrderId();
        }
        $cartInfo['cart_id'] = $id;
        array_push($cart, $cartInfo);
        Cookie::forever('cart', $cart);
        return ReturnFormat::json(true);
    }

    /**
     * 储存在redis中的格式：
     * 使用集合方式
     * key: cart[userid]
     * 以json字符串形式存放
     * @param $courseId
     */
    public function addToUserCart($courseId)
    {
        $authId = session('id');
        $cartKey = $this->cartPrefix . $authId;
        $courseInfo = $this->combineCourseInfo($courseId);
        if (!$courseInfo) return ReturnFormat::json('课程有误，不要乱来~', 'CART_COURSE_INFO_ERROR');
        $this->redis->sAdd($cartKey, $courseInfo);
        return ReturnFormat::json(true);
    }

    public function checkUserCartIfExisted($cartKey)
    {
        return $this->redis->exists($cartKey);
    }
    /**
     * 购物车ID获取，数据库有以数据库为准，数据库没有以cookie为准，都没有就生成
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCartId()
    {
        $userId = session('id');
        $cartId = -1;
        $vproCart = new VproCart();
        $res = $vproCart->where('cart_userid', '=', $userId)->find();
        // 数据库中的cartid优先，其次是cookie
        if ($res) {
            $res = $res->toArray();
            return (int)$res['cart_id'];
        } elseif (Cookie::get('cart')) {
            $cartId = Cookie::get('cart')[0]['cart_id'];
            $this->createCart($cartId, $userId);
            return $cartId;
        } else {
            // 都没有，就新建
//            $cartId = (new Snowflake())->getOrderId();
            $cartId = genCartId($userId);
            $this->createCart($cartId, $userId);
            return $cartId;
        }
    }

    /**
     * 创建一个用户的购物车信息条目
     * @param $cartId
     * @param $userId
     */
    public function createCart($cartId, $userId)
    {
        $vproCart = new VproCart();
        $vproCart->data([
            'cart_id'       =>  $cartId,
            'cart_addtime'  =>  time(),
            'cart_userid'   =>  $userId,
            'cart_status'   =>  1
        ]);
        $vproCart->save();
    }

    /**
     * 将商品放入购物车
     * @param $data
     * @return array|bool|false
     */
    public function addCartItems($data)
    {
        if (count($data) === 0) return true;
        try {
            $vproCartDetail = new VproCartDetail();
            return $vproCartDetail->saveAll($data);
        } catch (\Exception $e) {

            // 错误日志写入
            var_export($e);
            return false;
        }
    }
    public function getCartItemIds($cartId)
    {
        $vproCartDetail = new VproCartDetail();
        $coursesId = $vproCartDetail::where('cart_parent_id', $cartId)->column('cart_course_id');
        return $coursesId;
    }
    public function mergeCart()
    {
        $cartId = $this->getCartId();
        $existCourses = $this->getCartItemIds($cartId);
        if (Cookie::get('cart'))
        {
            $list = [];
            foreach(Cookie::get('cart') as $key => $item)
            {
                if (count($existCourses) > 0 && in_array($item['course_id'], $existCourses)) continue;
                array_push($list, [
                    'cart_course_id'    =>  $item['course_id'],
                    'cart_parent_id'    =>  $cartId,
                    'cart_add_time'     =>  $item['cart_add_time'],
                    'cart_is_cookie'    =>  0
                ]);
            }
            if ($this->addCartItems($list))
            {
                Cookie::delete('cart');
                return true;
            }
            return false;
        }
        return true;
    }
    public function checkCourses($courses)
    {
        $res = [
            'courses'   =>  [],
            'errs'      =>  [],
            'orderNo'   =>  -1
        ];
        $courseApi = new CourseApi();
        foreach($courses as $c)
        {
            $course = $courseApi->getCourseInfo($c['courseId'], 'course');
            if (!$course && (float)$course['course_price'] !== (float)$c['coursePrice']) {
                $res['errs'][] = $c['courseId'];
            } else {
                $res['courses'][] = $c['courseId'];
            }
        }
        return $res;
    }
    public function prePlaceOrder($ids)
    {
        // redis操作积分，在一定时间内超过限制次数就直接返回了
        Redis::scorePoint(config('key.REDIS_MISS'));

        $snowFlake = new Snowflake();

        $orderId = $snowFlake->getOrderId();

        if ($this->redis->setex($orderId, '720', json_encode($ids))) return (string)$orderId;
        return -1;
    }
}