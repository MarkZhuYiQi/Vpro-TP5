<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/12/2018
 * Time: 11:43 AM
 */
namespace app\common\controller;

class ReturnFormat
{
    static function json($data, $returnStatus='SUCCESS')
    {
        $code = is_numeric($returnStatus) ? $returnStatus : config('key.' . $returnStatus);
        return json_encode([
            'code' => $code,
            'data' => $data
        ]);
    }
}