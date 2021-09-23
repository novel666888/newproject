<?php

namespace common\controllers;

use common\BizResult;
use common\lib\Constant;
use common\lib\Request;
use common\models\Decrypt;
use common\models\Users;
use common\models\ValidModel;
use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use Overtrue\Pinyin;
use yii\web\Response;

class BaseController extends Controller
{
    public $enableCsrfValidation = false;

    public function init()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, weixins , Content-Type, Accept, x-file-name");
//        header("Access-Control-Allow-Headers:*");
        Yii::info(Request::input(), 'requestInfo');
        if (Yii::$app->getRequest()->isOptions) {
            exit;
        }
        parent::init();
    }

    /**
     * 开启事务
     * @return \yii\db\Transaction
     * @throws \yii\db\Exception
     */

    public function beginTransaction()
    {
        $trans = Yii::$app->db->beginTransaction();
        return $trans;
    }

    protected function verifyParam($defaultScenarios, $post, $rules, $is_throw_exception = true, $formName = "")
    {
        $check = new ValidModel();
        $check->setDefaultScenarios($defaultScenarios);
        $check->setRules($rules);
        $check->load($post, $formName);

        $res = $check->validate();

        if ($is_throw_exception && !$res) {
            foreach ($check->getErrors() as $key => $value) {
                BizResult::ensureNotFalseMsg(false, $value[0]);
            }
            BizResult::ensureNotFalse($res, Constant::WEB_ERROR_PARAM);
        }
        return $res;
    }

    public function renderJson($data)
    {
        $rt = [
            'code' => 0,
            'msg' => 'success',
            'data' => []
        ];

        if ($data instanceof \Exception) {
            $rt['code'] = $data->getCode() === 0 ? Constant::WEB_ERROR_PARAM : $data->getCode();
            $rt['msg'] = $data->getMessage();
        } else {
            $rt['data'] = $data;
        }
        $log['request_url'] = Yii::$app->request->pathInfo;
        $log['request_params'] = Request::input();
        $log['response'] = $rt;
        Yii::info(json_encode($log, JSON_UNESCAPED_UNICODE), 'process');
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $rt;
        return $response;
    }

    /**
     * getPostParam --获取post参数，为空时返回默认值
     * @param null $name
     * @param null $default
     * @return array|mixed
     * @cache No
     */
    protected function getPostParam($name = null, $default = null)
    {
        $request = Yii::$app->request;
        return $request->post($name, $default);
    }

    /**
     * getParam --获取参数，为空时返回默认值
     * @param null $name
     * @param null $default
     * @param string $method
     * @return array|mixed
     * @cache No
     */
    protected function getParam($name = null, $default = null, $method = 'get')
    {
        $request = Yii::$app->request;
        $method = strtolower($method);
        if ($method == 'post') {
            return $request->post($name, $default);
        } else {
            return $request->get($name, $default);
        }
    }

    /**
     * getRequestParam --获取参数，为空时返回默认值
     * @param null $name
     * @param null $default
     * @return array|mixed
     * @cache No
     */
    protected function getRequestParam($name = null, $default = null)
    {
        $request = Yii::$app->request;
        if ($name === null) {
            return ArrayHelper::merge($request->get(), $request->post());
        } else {
            return $request->post($name, $request->get($name, $default));
        }
    }

    protected function generateCommFilters(array &$filters)
    {
        return $filters;//extends this function to gen the common filters at every request
    }

    public function getParamWithRange($key, array $range, $default = null)
    {
        $value = $this->getParam($key, $default);
        return in_array($value, $range) ? $value : $default;
    }

    public function getParamWithIntRange($key, $min = 0, $max = 100, $default = 0)
    {
        $value = $this->getParam($key, $default);
        $value = intval($value);
        return $value >= $min && $value <= $max ? $value : $default;
    }

    /**
     *get request
     *
     * @return \yii\console\Request|\yii\web\Request
     */

    public function getRequest()
    {
        return \Yii::$app->getRequest();
    }

    /**
     * get session Object
     *
     * @return mixed|\yii\web\Session
     */

    public function getSession()
    {
        return \Yii::$app->session;
    }

    public function getUserInfo()
    {
        return \Yii::$app->session['userInfo'];
    }

    /**
     * get redis
     *
     * @return object
     */
    public function getRedis()
    {
        return \Yii::$app->redis;
    }

    /**
     * @params array $data
     * @return mixed
     */
    public function keyMod($data)
    {
        if (!$data || !is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $item) {
            if (is_string($key)) {
                $newKey = str_replace('_', ' ', strtolower($key));
                $newKey = lcfirst(str_replace(' ', '', ucwords($newKey)));
                unset($data[$key]);
                $data[$newKey] = $this->keyMod($item);
            } else {
                $data[$key] = $this->keyMod($item);
            }
        }
        return $data;
    }

    /**
     * 参数key转为小写+下划线
     *
     * @return void
     */
    protected function key2lower($data)
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
     * 下划线转驼峰
     * @param $data
     * @param string $separator
     * @return array
     */
    public static function key2lowerCamel($data, $separator = '_')
    {
        if (!$data || !is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $item) {
            if (is_string($key)) {
                if(strpos($key,$separator) == -1){
                    $newKey = $key;
                }else{
                    $newKey = lcfirst(\yii\helpers\Inflector::id2camel($key, $separator));
                }
                unset($data[$key]);
                $data[$newKey] = self::key2lowerCamel($item);
            } else {
                $data[$key] = self::key2lowerCamel($item);
            }
        }
        return $data;
    }

    /**
     * 获取当前毫秒时间戳
     *
     * @return number
     */
    public function getMicroTime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 防止刷新
     * @param $_this object 控制器某个方法内的$this
     * @param $seconds int 防止刷新间隔时间（单位秒，默认1S）
     * @param $sign string 请求身份标识符
     * @param $route string 访问路径字符串
     * @return bool true可以访问/false不可以访问
     */
    public function preventRefresh($_this="", $seconds=1, $sign="", $route=""){
        if(!empty($_this)){
            $route = "";
            $route.= isset($_this->module->id) ? $_this->module->id : "";
            $route.= isset($_this->id) ? $_this->id : "";
            $route.= isset($_this->action->id) ? $_this->action->id : "";
            if(empty($route)){
                return true;
            }
            $header = \Yii::$app->request->headers->toArray();
            if(!isset($header['weixins'][0])){
                return true;
            }else{
                $sign = trim($header['weixins'][0]);
            }
            if(empty($sign)){
                return true;
            }
            $sign = md5($sign);
        }
        if(empty($sign) || empty($route)){
            return true;
        }
        $redis = \Yii::$app->redis;
        $k="PR_".$sign."_".$route;
        $rs = $redis->keys($k);
        if(!empty($rs)){
            return false;
        }else{
            $redis->set($k,"1");
            $redis->expire($k, $seconds);
            return true;
        }
    }

    /**
     *   成功操作
     */
    public function jsonSuccess($msg='ok',$data=[],$code=200,$fill=[]){
        $returnData=[
            'code'=>$code,
            'data'=>$data,
            'msg'=>$msg,
            'fill'=>$fill
        ];

        return json_encode($returnData);
    }
    /**
     *   失败操作
     */
    public function jsonError($msg='操作失败',$data=[],$code=500){
        $returnData=[
            'code'=>$code,
            'data'=>$data,
            'msg'=>$msg,
        ];
        return json_encode($returnData);
    }

    /**
     *  汉字转拼音
     * @param $hanzi 需要转换的汉字 $type 1 返回字符串 2 返回数组
     * return 汉子匹配的拼音
     */
    public function hanZiToPinyin($hanzi,$type=1){
        $pinyin = new Pinyin\Pinyin();
        $data =$pinyin->convert($hanzi);
        if($type==1){
            return implode('',$data);
        }else{
            return $data;
        }
    }

    /**
     * 转换错误信息
     * @param $data
     * @return mixed
     */
    public function errorInfo($data){
        if(!empty($data)){
            foreach($data as $k=>$v){
                if(isset($v[0])) {
                    $v[0] = str_replace('"',"-",$v[0]);
                    return $v[0];
                }else{
                    $v;
                }
            }
        }
    }

    public function getUserInfoByToken(){
        $token = Decrypt::bossGetTokenInfo();
        if($token){
            $token = (array)$token;
            $userInfo = (new Users())->getUserInfoById($token['id']);
            return $userInfo;
        }
        exit($this->jsonError("登录已过期，请重新登录!",[],Constant::WEB_ERROR_TOKEN));
    }

    public function getUidByToken(){
        $token = Decrypt::bossGetTokenInfo();
        if($token){
            $token = (array)$token;
            $userInfo = (new Users())->getUserInfoById($token['id']);
            if(empty($userInfo))
                return $userInfo;
            return $userInfo['id'];
        }
        exit($this->jsonError("登录已过期，请重新登录!",[],Constant::WEB_ERROR_TOKEN));
    }

}