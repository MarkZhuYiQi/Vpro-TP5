<?php
namespace app\api;
use app\common\controller\Redis;
use app\common\model\VproCourses;
use app\common\model\VproCoursesCover;
use app\common\model\VproCoursesLessonList;
use think\Db;

class CourseApi extends BaseApi
{
    public function getCourseList($courseId) {
        if(Redis::checkRedisKey($courseId, 'VproLessonsList') || Redis::checkExpired($courseId, 'VproLessonsList')) {
            $list = Db::table('vpro_courses_lesson_list')->alias('vl')
                ->join('vpro_video vv', 'vv.video_lesson_id = vl.lesson_id and vl.lesson_is_chapter_head = 0', 'LEFT')
                ->join('vpro_video_detail vd', 'vd.detail_lesson_id = vl.lesson_id and vl.lesson_is_chapter_head = 0', 'LEFT')
                ->join('vpro_video_files vf', 'vf.video_file_lesson_id = vl.lesson_id and vl.lesson_is_chapter_head = 0', 'LEFT')
                ->where(['lesson_course_id' => $courseId])
                ->select();
            if (count($list) === 0) Redis::scorePSoint(config('key.DB_MISS'));
//            Redis::hsetex('VproLessonsList', $courseId, Redis::expired_time(0, 0), json_encode($list));
        } else {
            $list = json_decode(Redis::hgetex('VproLessonsList', $courseId));
        }
        return $list;
    }

    /**
     * 获得单个课程信息
     * @param $courseId
     * @return mixed
     */
    public function checkCourseExists($courseId)
    {
        return $this->redis->hGetAll('VproCourses' . $courseId);
    }

    public function genCourseList($list)
    {
        $headFlag = false;
        foreach($list as $value)
        {
            if (intval($value['lesson_is_chapter_head']) === 1)
            {
                $headFlag = true;
                break;
            }
        }
        $listTree = [];
        if ($headFlag)
        {
            foreach($list as $v)
            {
                if (intval($v['lesson_is_chapter_head']) === 1)
                {
                    $v['lesson'] = [];
                    foreach($list as $val)
                    {
                        if ($val['lesson_pid'] === $v['lesson_id'])
                        {
                            array_push($v['lesson'], $val);
                        }
                    }
                    array_push($listTree, $v);
                }
            }
        } else {
            $chapter = ['lesson' => $list, 'lesson_title' => false];
            array_push($listTree, $chapter);
        }
        return $listTree;
    }

    public function getCourseDetail($courseId)
    {
        $res = Db::table('vpro_courses')
            ->join('vpro_courses_cover', 'vpro_courses_cover.course_cover_id = vpro_courses.course_id', 'LEFT')
            ->join('vpro_courses_temp_detail', 'vpro_courses_temp_detail.course_id = vpro_courses.course_id', 'LEFT')
            ->join('vpro_auth', 'vpro_auth.auth_id = vpro_courses.course_author', 'LEFT')
            ->field([
                'vpro_courses.course_id',
                'vpro_courses.course_pid',
                'vpro_courses.course_title',
                'vpro_courses.course_price',
                'vpro_courses.course_author',
                'vpro_courses.course_discount_price',
                'vpro_courses_cover.course_cover_address',
                'vpro_auth.auth_appid',
                'vpro_courses_temp_detail.course_score',
                'vpro_courses_temp_detail.course_clickNum'
            ])
            ->where(['vpro_courses.course_id'=>$courseId])->select();
        if (count($res) === 0) {
            // 这里进行计分，如果db没有命中，说明是没有意义的课程id，涉嫌恶意访问，进行恶意积分累加
            Redis::scorePoint(config('key.DB_MISS'));
            return false;
        }
        return $res[0];
    }

    /**
     * 查询课程数据
     * @param $id
     * @param $infoType
     * @return bool
     */
    public function getCourseInfo($id, $infoType)
    {
        $keyType = [
            'cover'     =>  'VproCoursesCover',
            'course'    =>  'VproCourses',
            'detail'    =>  'VproTempDetail',
            'auth'      =>  'VproAuth'
        ];
        if (!array_key_exists($infoType, $keyType)) return false;
        $key = $keyType[$infoType] . $id;
        if (Redis::checkRedisKey($key))
        {
            return $this->redis->hGetAll($key);
        }
        else
        {
            $className = 'app\\common\\model\\' . $keyType[$infoType];
            $model = new $className();
            $data = $model::get($id);
            if (!$data) {
                Redis::scorePoint(config('key.DB_MISS'));
                return false;
            }
            return $this->setCourseCache($data, $key);
        }
    }
    public function setCourseCache($data, $key)
    {
        $data = $data->toArray();
        $this->redis->multi()
            ->hMset($key, $data)
            ->expire($key, config('index.COURSE_REDIS_SAVE_TIME'))
            ->exec();
        return $data;
    }
    public function setCourseClickNum($courseId)
    {
        $this->redis->zIncrBy('courses_click', 1, 'course' . $courseId);
    }
}