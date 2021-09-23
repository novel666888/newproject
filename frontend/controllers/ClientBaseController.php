<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/23
 * Time: 14:46
 */
namespace frontend\controllers;

use common\controllers\BaseController;
use common\lib\Common;
use common\lib\Constant;
use common\models\Decrypt;
use common\models\InterfaceService;
use common\models\Role;
use Yii;
use yii\helpers\ArrayHelper;

class ClientBaseController extends BaseController {

    public $userInfo = ['id' => '', 'identity' => '', 'phone' => ''];
    public $tokenInfo = null;

    public function init()
    {
        parent::init();
        //验证请求签名
        $this->checkEncryptParam();
        //验证登陆信息token
        $this->checkToken(Common::checkUrlWhiteList('whiteList'));
        //验证接口请求权限
        $this->authAction(Common::checkUrlWhiteList('permissionWhiteList'));
    }

    /**
     * 验证登录token
     * author: lijin
     * @param bool $check_login
     */
    public function checkToken($check_login = true)
    {
        if ($check_login) {
            $header = \Yii::$app->request->headers->toArray();
            if (empty($header['authorization'][0])) {
                exit($this->jsonError("缺少authorization参数",[],10001));
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
            }
        }
    }

    /**
     * 验证签名
     * author: lijin
     */
    public function checkEncryptParam()
    {
        $bossCheck = ArrayHelper::getValue(\Yii::$app->params,'clientSignCheckSwitch');
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
     * 验证接口权限
     * author: lijin
     * @param $isAuth
     * @param string $pathInfo
     * @return array|bool|mixed|\yii\db\ActiveRecord|null
     */
    public function authAction($isAuth, $pathInfo = ''){
        if(!$isAuth){
            return true;
        }
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