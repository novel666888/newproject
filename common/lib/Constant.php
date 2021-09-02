<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2020/10/21
 * Time: 18:23
 */
namespace common\lib;

class Constant{

    const SUCCESS_CODE = 0;
    const ERROR_CODE = 1;
    //10000的为web常见错误分类
    const WEB_ERROR_UNKNOWN = 10000;        //未知错误
    const WEB_ERROR_PARAM = 10001;          //参数错误
    const WEB_ERROR_PERMISSION = 10002;     //无权限
    const WEB_SIGN_TIMEOUT = 10003;         //签名超时
    const WEB_ERROR_SIGN = 10004;           //签名错误
    const WEB_ERROR_TOKEN = 10005;          //token过期
    const WEB_ERROR_LOGIN = 10006;          //登录错误
    const WEB_LOGIN_TIMEOUT = 10007;        //登录超时
    const ERROR_CODE_TOKEN_NULL = 10008;    //token为空
    const WEB_MODIFY_PASSWORD = 10009;      //提示修改密码

    const ERROR_CODE_SERVICE_API_METHOD_NOT_EXIST = 100001;  //接口地址不存在
}