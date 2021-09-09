<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/2
 * Time: 15:13
 */
namespace backend\logic;

class AuthLogic{

    public static function code2Session($code){
        $url = sprintf(AUTH_URL, APP_ID, APP_SECRET, $code);
        $response = file_get_contents($url);
        $result = json_decode($response, 1);
    }
}