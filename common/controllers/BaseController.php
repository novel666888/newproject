<?php

namespace common\controllers;

use common\BizResult;
use common\lib\Common;
use common\lib\Constant;
use common\models\Decrypt;
use common\models\InterfaceService;
use common\models\Role;
use common\models\Users;
use common\models\ValidModel;
use Yii;
use yii\base\UserException;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\services\Request;
use Overtrue\Pinyin;
use yii\web\Response;

class BaseController extends Controller
{
    private $i18nCategory = 'boss_auth';
    public $userInfo = ['id' => '273', 'identity' => '', 'phone' => '', ];
    public $tokenInfo = null;
    public $enableCsrfValidation = false;
    
    public function init()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
//        header("Access-Control-Allow-Headers: Origin, X-Requested-With, authorization , Content-Type, Accept, x-file-name");
        header("Access-Control-Allow-Headers:*");
        Yii::info(Request::input(), 'requestInfo');
        if (Yii::$app->getRequest()->isOptions) {
            exit;
        }
        parent::init();
        //验证请求签名
//        $this->checkEncryptParam(Common::checkUrlWhiteList('whiteList'));
        //验证请求操作权限
//        $this->authAction(Common::checkUrlWhiteList('whiteList'));
        //验证登陆信息
        $this->checkToken(Common::checkUrlWhiteList('whiteList'));
        //检查是否修改密码
//        $this->checkPassword();
    }

    public function checkPassword(){
        $user = Users::find()->where(['id' => $this->userInfo['id']])->asArray()->one();
        $origin_pass = md5('kpd'.md5(md5($user['phone'])).$user['slat']);
        $request = $this->getRequest();
        $newPass = $request->post('newPass');
        if ($origin_pass == $user['password'] && empty($newPass)){
            exit($this->jsonError("请修改密码！",[],Constant::WEB_MODIFY_PASSWORD));
        }
        return true;
    }

    public function checkToken($check_login = true)
    {
        try {
            if ($check_login) {
                $header = \Yii::$app->request->headers->toArray();
                if (empty($header['authorization'][0])) {
                     exit($this->jsonError("缺少authorization参数",[],10001));
//                    throw new UserException(\Yii::t($this->i18nCategory, ERROR_CODE_TOKEN_NULL), ERROR_CODE_TOKEN_NULL);
                }
            }

            $tokenInfo = Decrypt::bossGetTokenInfo();
            if ($tokenInfo) {
                $this->tokenInfo = $tokenInfo;
                if (isset($this->tokenInfo->sub) && !empty($this->tokenInfo->sub)) {
                    $info = explode("_", $this->tokenInfo->sub);
                    $this->userInfo['identity'] = isset($info[0]) ? $info[0] : '';
                    $this->userInfo['phone'] = isset($info[1]) ? $info[1] : '';
                    $this->userInfo['id'] = isset($info[2]) ? $info[2] : '';
                    Yii::info($this->userInfo, 'loginInfo');
                }
            }

            if ($check_login) {
                if (empty($this->userInfo['id'])) {
                    exit($this->jsonError("登录过期，请重新登录！",[],Constant::WEB_ERROR_TOKEN));
                //    throw new UserException(\Yii::t($this->i18nCategory, ERROR_CODE_TOKEN_ERROR), ERROR_CODE_TOKEN_ERROR);
                }
            }
        } catch (UserException $e) {
            if (in_array($e->getCode(), [Constant::ERROR_CODE_TOKEN_NULL, Constant::ERROR_CODE_TOKEN_NULL])) {
                $data['code'] = $e->getCode();
                $data['message'] = $e->getMessage();
                $data['data'] = [];
                echo json_encode($data, 256);
                Yii::info(['token' => $this->tokenInfo, 'data' => $data], 'loginInfo');
                exit;
            } else {
                throw $e;
            }
        }
    }

    /**
     * 开启事务
     *
     * @return \yii\db\Transaction
     * @throws \yii\db\Exception
     */

    public function beginTransaction()
    {
        $trans = Yii::$app->db->beginTransaction();
        //\Yii::$app->getDb()->createCommand("SET drds_transaction_policy = 'XA'")->execute();
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
            if(!isset($header['authorization'][0])){
                return true;
            }else{
                $sign = trim($header['authorization'][0]);
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

    public function checkEncryptParam($isAuth)
    {
        if(!$isAuth)
            return ;

        $bossCheck = ArrayHelper::getValue(\Yii::$app->params,'bossSignCheckSwitch');
        if($bossCheck == 1 && Common::checkUrlWhiteList('signCheckWhite')){
            $result = \Yii::$app->request->post();
            \Yii::info($result, 'getData');

            if(!isset($result['sign'])){
                echo json_encode(['code' => 403, 'message' => '签名参数验证失败!', 'data' => $result]); exit();
            }

            $result = array_filter($result, function($v) {
                if (!is_array($v)) {
                    return true;
                }
            });

            \Yii::info($result, 'result');
            $sign = trim($result['sign']);
            $dateline = trim($result['dateline']);
            if((time()-$dateline)>120){
                 exit($this->jsonError("签名已过期",[],Constant::WEB_SIGN_TIMEOUT));
            }
            unset($result['sign']);
            unset($result['dateline']);

            ksort($result);
            $param['dateline'] = $dateline;
            $param['secretKey'] = ArrayHelper::getValue(\Yii::$app->params,'secretKey');
            \Yii::info($result, 'paramresult');

            $param = http_build_query($param, null,'&', PHP_QUERY_RFC3986);

            \Yii::info($param, 'param');
            $checkSign = md5($param);
            \Yii::info($checkSign, 'checkSign');
            if( $sign != $checkSign ){
                echo json_encode(['code' => 403, 'message' => '签名参数验证失败!', 'data' => [$sign=>$checkSign]]); exit();
            }
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
    /**

    /**
     * 验证权限
     */
    public function authAction($isAuth,$pathInfo=''){

        if(!$isAuth)
            return ;

        $user = $this->getUserInfoByToken();
        if(!is_array($user)){
            return $user;
        }

        $pathInfo = $pathInfo=='' ? Yii::$app->getRequest()->pathInfo : $pathInfo;
        $status = (new InterfaceService())->validateAuth($pathInfo);

        if($status===false){
            exit($this->jsonError("操作不存在"));
        }
        //白名单的接口
        if($status===true){
            return $status;
        }

        //需要验证的接口地址
        if(is_numeric($status)){
            $isAuth = (new Role())->roleAuth($user['role_id'],$status);
            if($isAuth===false){
                exit($this->jsonError("无权限操作",[],Constant::WEB_ERROR_PERMISSION));
            }
            return true;
        }

    }
}