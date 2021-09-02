<?php

namespace common\lib;

use yii;
use yii\web\Response;

/**
 * Class Json --格式化json数据
 * @package common\util
 */
class Json
{
    public static function bool($result, $data = [])
    {
        if ($result) {
            return self::success($data);
        }

        return Json::error($data);
    }

    public static function data($data = [])
    {
        if (FALSE === $data) {
            return self::error("失败");
        }

        if ($data['code'] != 0) {
            return self::error(isset($data['data']) ? $data['data'] : "", $data['code'], $data['msg']);
        }

        return self::success($data['data']);
    }

    public static function success($data = [])
    {
//        if (!$data){
//            $data = new \stdClass();
//        }
        return self::output(200, "success", $data);
    }

    public static function partialSuccess($success = [], $failed = [], $extra = [])
    {
        return self::output(2, "error", [
            'success' => $success,
            'fail' => $failed,
            'extra' => $extra,
        ]);
    }

    public static function error($data = [], $code = Constant::WEB_ERROR_PARAM, $message = "error")
    {
        return self::output($code, "error" == $message && !empty($data) && is_string($data) ? $data : $message, $data);
    }

    public static function message($message = 'success', $code = Constant::WEB_ERROR_PARAM)
    {
        return self::output($code, $message, new \stdClass());
    }

    private static function output($code, $message, $data)
    {
        if (php_sapi_name() !== "cli") {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
        $data = [
            'code' => $code,
            'msg' => $message,
            'data' => !empty($data) ?$data : [],
        ];

        $log['request_url'] = Yii::$app->request->pathInfo;
        $log['request_params'] = Request::input();
        $log['response'] = $data;
        Yii::info(json_encode($log, JSON_UNESCAPED_UNICODE), 'process');

        return $data;
    }

    public static function emptyList()
    {
        return self::success(['totalCount' => 0, 'list' => []]);
    }
}