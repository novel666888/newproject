<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2020/10/9
 * Time: 10:15
 */
namespace common\lib;

use common\models\Organize;
use common\models\Upload;
use common\models\Users;
use yii;
class Common{
    /**
     * checkUrlWhiteList --请求地址白名单校验
     * @param $paramWhiteListKey
     * @return bool
     * @cache No
     */
    public static function checkUrlWhiteList($paramWhiteListKey)
    {
        $pathInfo = Yii::$app->getRequest()->pathInfo;

        if (substr($pathInfo, 0, 1) == '/') {
            $pathInfo = substr($pathInfo, 1);
        }

        $whiteList = Yii::$app->params[$paramWhiteListKey];
        return (in_array('/', $whiteList) || in_array($pathInfo, $whiteList)) ? false : true;
    }

    /**
     * 过滤用户输入的基本数据，防止script攻击
     *
     * @param      string
     * @return     string
     */
    public static function compile_str($str)
    {
        $arr = array('<' => '＜', '>' => '＞','"'=>'”',"'"=>'’');
        return strtr($str, $arr);
    }

    /**
     * 验证手机号
     *
     * @param string $phoneNum
     * @return number
     */
    public static function checkPhoneNum($phoneNum)
    {
        return preg_match("/^1[34578]{1}\d{9}$/",$phoneNum);
    }

    /**
     * 验证网址
     *
     * @param $url
     * @return bool
     */
    public static function checkUrl($url)
    {
        if(!preg_match('/http|https:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
            return false;
        }
        return true;
    }

    /**
     * 验证邮箱
     * author: lijin
     * @param $email
     * @return bool
     */
    public static function checkEmail($email){
        if(!preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,})$/",$email)){
            return false;
        }
        return true;
    }

    /**
     * @param $url
     * @param null $data
     * @param string $method
     * @return bool|string
     */
    public static function http_request($url, $data = null, $method = 'get')
    {
        set_time_limit(0);
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 300);
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $headers = [
            "authorization:".$_SERVER['HTTP_AUTHORIZATION'],
            "Content-Type:application/json",
            "Access-Token:".$_SERVER['HTTP_ACCESS_TOKEN'],
            'X-Debug-Mode' => $_SERVER['HTTP_X_DEBUG_MODE']
        ];
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        if ($method == 'post') {
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($oCurl, CURLOPT_URL, $url);
        }

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    public static function key2lower($data)
    {
        if (!$data || !is_array($data)) {
            return $data;
        }
        foreach ($data as $_k => $_v) {
            $_key = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
                return '_' . strtolower($matchs[0]);
            }, $_k);
            if ($_key != $_k) {
                $data[$_key] = $_v;
                unset($data[$_k]);
            }
        }
        return $data;
    }

    /**
     * 新老后台同步请求
     * author: lijin
     * @param $url
     * @param $data
     * @param int $type
     * @param array $header
     * @return bool|string
     */
    public static function OwenHttpRequest($url, $data, $type=0, $header=[]){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        $type && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * 操作日志
     * author: lijin
     * @param $params
     * @param $response
     * @param $mark
     * @throws \Exception
     */
    public static function adminLog($params, $response, $mark)
    {
        $module = \Yii::$app->controller->module->id;
        $root_path = \Yii::getAlias('@mdklog');
        $module_path = $root_path .'/'. $module;
        if (!file_exists($module_path)){
            mkdir($module_path, 0777);
        }
        $logFile = $module_path .'/'. $module .'_'. date("Ymd").'.log';
        $request = \Yii::$app->getRequest();
        try {
            $requestData = @$request->post();
        } catch (\Exception $exception) {
            $requestData = $request->params[0];
        }
        $params = is_array($params) ? json_encode($params, 256) : $params;
        $response = is_array($response) ? json_encode($response, 256) : $response;
        $ip = \Yii::$app->request->userIP ?? '-';
        $controller = \Yii::$app->controller->id;
        $action = \Yii::$app->controller->action->id;
        $message = "[" . (new \DateTime())->format('Y-m-d H:i:s,u') . "][mark:" . $mark . "][{$ip}][{$controller}/{$action}][request:" . json_encode($requestData,256) . "][params:" . $params . "][response:" . $response . "]\n";
        error_log($message, 3, $logFile);
    }

    
    /*
     * 操作日志
     */
    public static function adminLogs($params, $response, $mark)
    {
        $module = \Yii::$app->controller->module->id;
        $root_path = \Yii::getAlias('@mdklog');
        $module_path = $root_path .'/'. $module;
        if (!file_exists($module_path)){
            mkdir($module_path, 0777);
        }
        $logFile = $module_path .'/'. $mark .'_'. date("Ymd").'.log';
        $params = is_array($params) ? json_encode($params, 256) : $params;
        $response = is_array($response) ? json_encode($response, 256) : $response;
        $message = "[" . (new \DateTime())->format('Y-m-d H:i:s') . "][params:" . $params . "][response:" . $response . "]\n";
        error_log($message, 3, $logFile);
    }
    /**
     * 根据部门或者用户获取下级所有用户ids
     * author: lijin
     * @param $user_id
     * @param $params
     * @return array
     * @throws \Exception
     */
    public static function get_user_ids($user_id, $params){
        $user = new Users();
        $user_info = $user->getUserInfoById($user_id, 0);//取当前用户所有下级成员
        Common::adminLog(['user_id' => $user_id], $user_info['authUid'], 'get-organize-user');
        $departmentId = !empty($params['groupId']) ? $params['groupId'] : (!empty($params['teamId']) ? $params['teamId'] : (!empty($params['departmentId']) ? $params['departmentId'] : ''));
        if (empty($departmentId)){
            $user_ids = $user_info['authUid'];
        }elseif (!empty($departmentId) && empty($params['userId'])){
            $organize = new Organize();
            $son_organize = $organize->getOrganizeNameById($departmentId,2);//取组织架构所有下级组织架构
            $son_user = $user->getUserInfoByOrganizeId($son_organize['organizeIds'][0],1);//取所有用户
            $user_ids = array_column($son_user, 'id');
        }else{
            $user_ids = [$params['userId']];
        }
        return ['user_ids' => $user_ids, 'role_id' => $user_info['role_id'], 'department' => $departmentId];
    }

    /**
     * 二维数组根据字段去重
     * author: lijin
     * @param $array
     * @param $key
     * @return array
     */
    public static function uniqueArr($array, $key)
    {
        $result = array();
        foreach ($array as $k => $val) {
            $code = false;
            foreach ($result as $_val) {
                if ($_val[$key] == $val[$key]) {
                    $code = true;
                    break;
                }
            }
            if (!$code) {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * 生成id
     * author: lijin
     * @return string
     */
    public static function getId(){
        return base_convert(uniqid(), 16, 10);
    }

    /**
     * 上传文件
     * author: lijin
     * @param string $path --指定文件夹
     * @return string
     */
    public static function uploadFile($path = 'default'){
        $model = new Upload();
        $tmpPath = \Yii::getAlias('@filecache').$path;
        if (!file_exists($tmpPath)){
            mkdir($tmpPath, 0777);
        }
        if (\Yii::$app->request->isPost) {
            $model->file = yii\web\UploadedFile::getInstanceByName('file');
            $fileName = date('YmdHis').rand(10000,99999);
            $filePath = $tmpPath .'/'. $fileName . '.' . $model->file->extension;
            if ($model->file && $model->validate()) {
                $model->file->saveAs($filePath);
            }
        }
        return ['fileName'=>$path.'/'.$fileName.'.'.$model->file->extension];
    }

    /**
     * 树结构组装
     * author: lijin
     * @param $arr
     * @param $pid  --父级id的key
     * @return array
     */
    public static function tree($arr, $pid='pid'){
        $tree = [];
        $list = [];
        foreach($arr as $k => $v){
            $list[$v['id']] = $v;
            if ($v[$pid] == 0){
                $tree[] = &$list[$v['id']];
            }
        }
        foreach ($list as $kk => $vv) {
            $list[$vv[$pid]]['children'][] = &$list[$kk];
        }
        return $tree;
    }
    /**
     * 获取下周一
     * huohaoqing
     * @return false|string
     */
    public static function getNextMonday()
    {
        return strtotime(date('Y-m-d',strtotime('+1 week last monday')));
    }

    /**
     * 获取下周日
     * huohaoqing
     * @return false|string
     */
    public static  function getNextSunday()
    {
        return strtotime(date('Y-m-d',strtotime('+1 week last monday')))+518400;
    }

    /**
     * 本周一
     * huohaoqing
     * @param int $timestamp
     * @param bool $is_return_timestamp
     * @return false|int|string
     */
    public static function  this_monday($timestamp=0,$is_return_timestamp=true){
        static $cache ;
        $id = $timestamp.$is_return_timestamp;
        if(!isset($cache[$id])){
            if(!$timestamp) $timestamp = time();
            $monday_date = date('Y-m-d', $timestamp-86400*date('w',$timestamp)+(date('w',$timestamp)>0?86400:-/*6*86400*/518400));
            if($is_return_timestamp){
                $cache[$id] = strtotime($monday_date);
            }else{
                $cache[$id] = $monday_date;
            }
        }
        return $cache[$id];

    }

    /**
     * 本周日
     * huohaoqing
     * @param int $timestamp
     * @param bool $is_return_timestamp
     * @return false|int|string
     */
    public static function this_sunday($timestamp=0,$is_return_timestamp=true){
        static $cache ;
        $id = $timestamp.$is_return_timestamp;
        if(!isset($cache[$id])){
            if(!$timestamp) $timestamp = time();
            $sunday = self::this_monday($timestamp) + /*6*86400*/518400;
            if($is_return_timestamp){
                $cache[$id] = $sunday;
            }else{
                $cache[$id] = date('Y-m-d',$sunday);
            }
        }
        return $cache[$id];
    }

    /**
     * 上周一
     * huohaoqing
     * @param int $timestamp
     * @param bool $is_return_timestamp
     * @return false|int|string
     */
    public static  function last_monday($timestamp=0,$is_return_timestamp=true){
        static $cache ;
        $id = $timestamp.$is_return_timestamp;
        if(!isset($cache[$id])){
            if(!$timestamp) $timestamp = time();
            $thismonday = self::this_monday($timestamp) - /*7*86400*/604800;
            if($is_return_timestamp){
                $cache[$id] = $thismonday;
            }else{
                $cache[$id] = date('Y-m-d',$thismonday);
            }
        }
        return $cache[$id];
    }

    /**
     * 上周日
     * huohaoqing
     * @param int $timestamp
     * @param bool $is_return_timestamp
     * @return false|int|string
     */
   public static function  last_sunday($timestamp=0,$is_return_timestamp=true){
        static $cache ;
        $id = $timestamp.$is_return_timestamp;
        if(!isset($cache[$id])){
            if(!$timestamp) $timestamp = time();
            $thissunday = self::this_sunday($timestamp) - /*7*86400*/604800;
            if($is_return_timestamp){
                $cache[$id] = $thissunday;
            }else{
                $cache[$id] = date('Y-m-d',$thissunday);
            }
        }
        return $cache[$id];
    }
}

