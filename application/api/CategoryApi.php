<?php
/**
 * Created by PhpStorm.
 * User: Red
 * Date: 2018/6/28
 * Time: 23:13
 */
namespace app\api;
use app\common\controller\Redis;
use app\common\model\VproCourses;
use think\Db;

class CategoryApi
{
    protected $redis;
    function __construct()
    {
        $this->redis = Redis::getRedisHandler();
    }
    function getCourses ($navIds, $page=1)
    {
        $offset = ($page - 1) * (int)config('index.COURSES_OFFSET');
        $coursesOffset = config('index.COURSES_OFFSET');
        $navIds = implode(',', $navIds);
        // 优化分页有2中方式：1. 在唯一主键上下功夫，以某一个位置为分界点，大于或小于这个值并limit；2. 先join将范围内的主键搜索出来，再进行查找
        $query = <<<QUERY
SELECT
	course.course_id,
	course.course_time,
	course.course_price,
	course.course_discount_price,
	course.course_title,
	course_cover.course_cover_key,
	course_cover.course_cover_address,
	course_cover.course_cover_isuploaded,
	course_cover.course_cover_isused,
	course_detail.course_score,
	course_detail.course_clickNum,
	va.auth_appid AS course_author
FROM
	vpro_courses_temp_detail AS course_detail
LEFT JOIN vpro_courses_cover AS course_cover ON course_detail.course_id = course_cover.course_cover_id
LEFT JOIN vpro_courses AS course ON course_detail.course_id = course.course_id
LEFT JOIN vpro_auth AS va ON va.auth_id = course.course_author
WHERE
	course_detail.course_id IN (
		SELECT
			c.course_id
		FROM
			(
				SELECT
					course_id
				FROM
					vpro_courses_temp_detail
				WHERE
					course_pid IN ($navIds)
				ORDER BY
					course_clickNum DESC
				LIMIT $coursesOffset OFFSET $offset
			) as c
	)
ORDER BY
	course_detail.course_clickNum DESC
QUERY;
        $res = Db::query($query);
        return $res;
    }
    function getTotalCourses($navIds)
    {
        $navIds = implode(', ', $navIds);
        // bug，为何第一个被切掉了
        $navIds = '-1, ' . $navIds;
        $total = VproCourses::where('course_pid', 'IN', '(' . $navIds . ')')->count('course_id');
        return $total;
    }

}