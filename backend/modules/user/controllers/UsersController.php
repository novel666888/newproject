<?php

namespace backend\modules\user\controllers;

use backend\logic\AccountLogic;
use common\models\Action;
use common\models\Decrypt;
use common\models\RoleToAction;
use common\models\UserHomeConfig;
use common\models\UserPosition;
use common\services\FlyBookNotice;
use common\services\traits\ModelTrait;
use Yii;
use common\controllers\BaseController;
use common\models\Organize;
use common\models\Role;
use common\models\Users;
use yii\captcha\Captcha;
use yii\captcha\CaptchaAction;
use yii\data\Pagination;

class UsersController extends BaseController
{
    use ModelTrait;
    const PASSKEY="kpd";

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength' => 4,
                'minLength' => 4,
                'foreColor' => 0x000000,
                'offset' => 10,
                'transparent' => true,
            ],
        ];
    }

    /**
     * 获取用户菜单
     * @return false|string
     */
    public function actionMenu()
    {
        $userInfo = $this->getUserInfoByToken();
        $menu['menu'] = (new Role())->getMenuByRid($userInfo['role_id']);
        $menu['video_role'] = 0;
        if (in_array($userInfo['role_id'], VIDEO_RADAR_PART)){
            $menu['video_role'] = 1;
        }
        $this->userLog($userInfo['id']);
        return $this->jsonSuccess("获取成功！",$menu);
    }

    /**
     * 修改密码
     */
    public function actionRepass(){
        $param = Yii::$app->request->post();
        if(!isset($param['originalPass']) || empty($param['originalPass'])){
            return $this->jsonError("originalPass参数异常",[],400);
        }

        if(!isset($param['newPass']) || empty($param['newPass'])){
            return $this->jsonError("newPass参数异常",[],400);
        }

        $userInfo = $this->getUserInfoByToken();
        $originalPass =  md5(self::PASSKEY.$param['originalPass'].$userInfo['slat']);

        if($userInfo['password'] != $originalPass){
            return $this->jsonError("原始密码不正确",[],400);
        }

        $newPass = md5(self::PASSKEY.$param['newPass'].$userInfo['slat']);
        $status = Users::updateAll(['password'=>$newPass],['id'=>$userInfo['id']]);
        if(is_numeric($status)){
            return $this->jsonSuccess("修改成功!");
        }else{
            return $this->jsonError("修改失败");
        }
    }
    /**
     * 重置密码
     */
    public function actionResetPass(){
        $param = Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("参数异常，缺少用户ID",[],400);
        }

        $userData = Users::findOne($param['id'])->toArray();

        if(empty($userData)){
            return $this->jsonError("用户不存在",[],400);
        }

        $newPass = md5(md5($userData['phone']));
        $newPass = md5(self::PASSKEY.$newPass.$userData['slat']);
        $status = Users::updateAll(['password'=>$newPass],['id'=>$userData['id']]);
        if(is_numeric($status)){
            return $this->jsonSuccess("重置成功");
        }else{
            return $this->jsonError("重置失败");
        }

    }
    /**
     * 用户登录
     */
    public function actionLogin(){
         $param = Yii::$app->request->post();
         if(!isset($param['phone']) || empty($param['phone'])){
             return $this->jsonError("phone参数异常",[],400);
         }
        if(!isset($param['password']) || empty($param['password'])){
            return $this->jsonError("password参数异常",[],400);
        }
//        if (empty($param['captcha'])){
//            return $this->jsonError("请填写验证码",[],400);
//        }
//        if (!$this->createAction('captcha')->validate($param['captcha'], false)){
//            return $this->jsonError("验证码不正确",[],401);
//        }
        $userData = Users::find()->select("id,username,password,phone,email,worked,role_id,role_name,organize_id,organize_name,slat")->where(['phone'=>$param['phone'],'worked'=>1])->asArray()->one();
        if(empty($userData)){
            return $this->jsonError("手机号不存在",[],401);
        }

        $pwd = md5(self::PASSKEY.$param['password'].$userData['slat']);

        if($userData['password']!=$pwd){
            return $this->jsonError("您输入的密码错误",[],401);
        }

        $token = Decrypt::createBossToken($userData['id']);
        if($token!==false){
            unset($userData['password'],$userData['slat']);
            $userData['token']=$token;
            Yii::$app->session['userInfo']=$userData;
            return $this->jsonSuccess("登录成功！",$userData);
        }
        return $this->jsonError("未能成功生成token",[]);
    }
    /**
     *  创建用户
     */
    public function actionCreate(){
        $param =  Yii::$app->request->post();
        $param = $this->createParamCheck($param);
        if(!is_array($param)){
            return $param;
        }
        $model = new Users();
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save($param)){
            return $this->jsonSuccess("创建成功");
        }else{
            return $this->jsonError("创建失败");
        }
    }

    public function createParamCheck($param){
        if(!isset($param['username']) || empty($param['username'])){
            return $this->jsonError("请输入姓名！",[],400);
        }
        if(!isset($param['sex']) || empty($param['sex'])){
            return $this->jsonError("请输入性别！",[],400);
        }
        if(!isset($param['phone']) || empty($param['phone'])){
            return $this->jsonError("请输入手机号！",[],400);
        }

        if(!isset($param['role_id']) || empty($param['role_id'])){
            return $this->jsonError("请选择角色！",[],400);
        }else{
            $param['role_name'] = (new Role())->getRoleNameById($param['role_id']); // 冗余角色ID
        }

        if(!isset($param['organize_id']) || empty($param['organize_id'])){
            return $this->jsonError("请选择部门！",[],400);
        }else{
            $param['organize_name'] = (new Organize())->getOrganizeNameById($param['organize_id'],0); // 冗余所在组织架构名称
            $parendData =  (new Organize())->getOrganizeNameById($param['organize_id'],-1); // 冗余所属组织架构名称
            $param['parent_organize_id']   = $parendData['parent_organize_id'];
            $param['parent_organize_name'] = $parendData['parent_organize_name'];
        }
        if(!isset($param['email']) || empty($param['email'])){
            return $this->jsonError("请填写用户邮箱！",[],400);
        }
        $flyData = (new FlyBookNotice())->getFlyUidByPhone($param['phone']);
        if(empty($flyData)){
            return $this->jsonError("系统异常！");
        }

        if($flyData['code']==404){  // 公司外部人员不验证 飞书
            $param['fly_user_id']='';
            $param['fly_open_id']='';
            //return $this->jsonError($flyData['msg'],[],400);//不是飞书绑定的用户手机号不可以进行创建
        }else{
            $param['fly_user_id']=$flyData['data']['user_id'];
            $param['fly_open_id']=$flyData['data']['open_id'];
        }

        $salt = substr(md5(uniqid()),2,8);
        $param['slat']=$salt;
        $md5Phone = md5(md5($param['phone']));
        $param['password']=md5(self::PASSKEY.$md5Phone.$salt);
        return $param;
    }

    /**
     * 根据用户姓名输出用户姓名全拼
     * @return false|string
     */
    public function actionGenerateEmail(){
        $param =  Yii::$app->request->post();
        if(!isset($param['username'])){
            return $this->jsonError("请输入用户全称！");
        }

        $cnt = Users::find()->where(['username'=>$param['username']])->count();
        $pinyin = $this->hanZiToPinyin($param['username']);
        if($cnt>0){
            $email = $pinyin.$cnt."@maidike.top";
        }else{
            $email = $pinyin."@maidike.top";
        }
       return $this->jsonSuccess("生成成功！",['email'=>$email]);
    }

    /**
     *  修改用户信息
     */
    public function actionUpdate(){
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
             return $this->jsonError("请输入用户id！");
        }
        $data = Users::findOne(['id'=>$param['id']]);
        if(empty($data)){
             return $this->jsonError("用户不存在");
        }
        if($data['worked']==2){
            return $this->jsonError("员工已经离职，不允许修改！");
        }
        $saveUid=$param['id'];
        unset($param['id']);
        if($param['worked']==2){
            $param['departure_time']=date("Y-m-d H:i:s");
        }
        $originalOrganizeId=$data['organize_id'];
        if(isset($param['role_id']) && !empty($param['role_id'])){
            $param['role_name'] = (new Role())->getRoleNameById($param['role_id']); // 冗余角色名称
        }
        if(isset($param['phone']) && !empty($param['phone'])){
            $flyData = (new FlyBookNotice())->getFlyUidByPhone($param['phone']);
            if(empty($flyData)){
                return $this->jsonError("系统异常！");
            }
            if($flyData['code']==404){
                $param['fly_user_id']='';
                $param['fly_open_id']='';
                //return $this->jsonError($flyData['msg'],[],400);
            }else{
                $param['fly_user_id']=$flyData['data']['user_id'];
                $param['fly_open_id']=$flyData['data']['open_id'];
            }
        }
        if(isset($param['organize_id']) && !empty($param['organize_id'])){
            $param['organize_name'] = (new Organize())->getOrganizeNameById($param['organize_id'],0); // 冗余所在组织架构名称
            $parendData =  (new Organize())->getOrganizeNameById($param['organize_id'],-1); // 冗余所属组织架构名称
            $param['parent_organize_id']   = $parendData['parent_organize_id'];
            $param['parent_organize_name'] = $parendData['parent_organize_name'];
        }

        $data->load($param,'');
        if(!$data->validate()){
            return $this->jsonError($this->errorInfo($data->getFirstErrors()));
        }

        if($data->save()){
            if($originalOrganizeId != $param['organize_id']){
                 $params = ['optimizer_id'=>$saveUid];
                 $saveData = Organize::find()
                     ->select("top_one_id,top_one_name,top_two_id,top_two_name,top_three_id,top_three_name,top_four_id,top_four_name")
                     ->where(['id'=>$param['organize_id']])
                     ->asArray()
                     ->one();
                 AccountLogic::SaveAdinfo($params,$saveData);
            }
            return $this->jsonSuccess("修改成功");
        }else{
            return $this->jsonError("修改失败",$this->errorInfo($data->getFirstErrors()));
        }
    }

    /**
     *  查询用户列表
     */
    public function actionSelect(){
        $param =  Yii::$app->request->post();
        $where=['and'];
        if(isset($param['username']) && !empty($param['username'])){
            $username=trim($param['username']);
            //$where['username']=$username;
            $where[]=['like','username',$username];
        }
        if(isset($param['role_id']) && !empty($param['role_id'])){
            $roleId=trim($param['role_id']);
            $where[]=['role_id'=>$roleId];
        }
        if(isset($param['phone']) && !empty($param['phone'])){
            $phone=trim($param['phone']);
            $where[]=['phone'=>$phone];
        }
        if(!isset($param['page']) || empty($param['page'])){
            $param['page']=1;
        }
        if(!isset($param['pageSize']) || empty($param['pageSize'])){
            $param['pageSize']=ModelTrait::$defaultPageSize;
        }

        $model = Users::find()->where($where);
        $pagesOffset = ($param['page']-1)*$param['pageSize'];
        $userInfo = $model->select('id,username,phone,email,role_name,parent_organize_name,organize_name,worked,create_time,departure_time')->offset($pagesOffset)
            ->limit($param['pageSize'])
            ->asArray()
            ->all();
//        foreach($userInfo as $k=>$v){
//            if(!empty($v['departure_time'])){
//                $time = strtotime($v['departure_time']);
//            }
//        }
        $count = Users::find()->where($where)->count();
        $data = [
            'list'=>$userInfo,
            'count'=>(int)$count,
            'page'=>$param['page'],
            'pageSize'=>$param['pageSize'],
            'pageCount'=>ceil($count/$param['pageSize']),
        ];
        return $this->jsonSuccess('获取成功！',$data);
    }

    /**
     *  查询用户详情
     */
    public function actionGetUserInfoById(){
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("缺少用户ID");
        }
        $data = (new Users())->getUserInfoById($param['id']);
        return $this->jsonSuccess("获取成功",$data);
    }

    /**
     * 获取老平台token
     */
    public function actionGetOldToken(){
        $userInfo = $this->getUserInfoByToken();
        $dateLine = time();
        $sign=substr(md5($userInfo['phone'].$dateLine),3,9);
        $data = [
            'phone'=>$userInfo['phone'],
            't'=>$dateLine,
            'c'=>$sign
        ];
        return $this->jsonSuccess("参数生成成功",$data);
    }
    /**
     *  根据用户ID获取用户手机号
     */
    public function actionGetPhoneByUid(){
        $param =  Yii::$app->request->post();
        if(!isset($param['uid']) || !is_numeric($param['uid'])){
            return $this->jsonError("参数错误,缺少用户ID");
        }
        $userData = Users::find()
            ->select("phone")
            ->where(['id'=>$param['uid']])
            ->asArray()
            ->one();
        return $this->jsonSuccess("ok",$userData);
    }
    /**
     * 获取新平台token
     */
    public function actionTokenLogin(){
        $param =  Yii::$app->request->post();
        if(!isset($param['phone']) || empty($param['phone'])){
            return $this->jsonError("参数错误,缺少phone");
        }
        if(!isset($param['t']) || empty($param['t'])){
            return $this->jsonError("参数错误，缺少dateLine");
        }
        if(!isset($param['c']) || empty($param['c'])){
            return $this->jsonError("参数错误，缺少sign");
        }
        if((time()-$param['t'])>120){
            return $this->jsonError("sign效验失败，签名已过期");
        }
        $sign =  substr(md5($param['phone'].$param['t']),3,9);
        if($param['c']!=$sign){
            return $this->jsonError("c效验失败，签名效验不通过");
        }
        $userData = Users::find()
            ->select("id,username,phone,email,worked,role_id,role_name,organize_id,organize_name,slat")
            ->where(['phone'=>$param['phone'],'worked'=>1])
            ->asArray()
            ->one();
        if(empty($userData)){
            return $this->jsonError("用户不存在");
        }
        $token = Decrypt::createBossToken($userData['id']);
        if($token===false){
            return $this->jsonError("token生成失败!");
        }
        $userData['token']=$token;
        Yii::$app->session['userInfo']=$userData;
        return $this->jsonSuccess("登录成功！",$userData);
    }

    /**
     * 查询用户首页拖拽定位
     */
    public function actionGetPosition(){
        $param = Yii::$app->request->post();
        if(!isset($param['menu_id']) || !is_numeric($param['menu_id'])){
            return $this->jsonError("请输入页面ID");
        }
        $userId = $this->userInfo['id'];
        if(!$userId){
            return $this->jsonError("未找到用户ID");
        }
        $where = ['user_id'=>$userId,'action_id'=>$param['menu_id']];
        $data =  UserPosition::findOne($where);
        if($data){
            $data = $data->toArray();
            $data['content'] = json_decode($data['content'],true);
            return $this->jsonSuccess("ok",$data);
        }else{
            return $this->jsonSuccess("未查询到数据",[]);
        }
    }

    /**
     * 记录
     */
    public function actionSavePosition(){
        $param = Yii::$app->request->post();
        $userId = $this->userInfo['id'];
        if(!isset($param['content']) || empty($param['content'])){
            return $this->jsonError("请输入定位内容");
        }
        if(!isset($param['menu_id']) || !is_numeric($param['menu_id'])){
            return $this->jsonError("请输入页面ID");
        }
        $where = ['and'];
        $where[]=["action_id"=>$param['menu_id']];
        $where[]=["user_id"=>$userId];
        $data = UserPosition::find()->where($where)->one();
        if($data){
            $data->content = json_encode($param['content']);
            if($data->save(false)){
                return $this->jsonSuccess("修改成功");
            }else{
                return $this->jsonError("修改失败",$data->errors);
            }
        }else{
           $model = new  UserPosition();
           $addData = [
               "user_id"=>$userId,
               "content"=>json_encode($param['content']),
               "action_id"=>$param['menu_id']
           ];
           if($model->load($addData,'') && $model->save()){
               return $this->jsonSuccess("添加成功");
           }else{
               return $this->jsonError("添加失败",$model->errors);
           }
        }
    }

    /**
     * 获取某个模块下按钮权限
     */
    public function actionGetButton(){
        $param = Yii::$app->request->post();
        if(!isset($param['module_id']) || !is_numeric($param['module_id'])){
            return $this->jsonError("缺少模块ID");
        }
        $userInfo = $this->getUserInfoByToken();
        $roleId = $userInfo['role_id'];
        $data = RoleToAction::find()
            ->alias("ra")
            ->select("a.id,a.name")
            ->leftJoin(Action::tableName() . " as a","ra.aid = a.id")
            ->where([
                'and',
                ['ra.rid'=>$roleId],
                ['a.pid'=>$param['module_id']]
            ])
            ->asArray()->All();
        return $this->jsonSuccess("ok",$data);
    }

    /**
     * 权限下所有模块
     */
    public function actionModulesAll(){
        $param = Yii::$app->request->post();
        if(!isset($param['aid'])){
            return $this->jsonError("缺少菜单ID");
        }
        $userInfo = $this->getUserInfoByToken();
        //获取首页菜单ID
        $where=['and'];
        //获取当前角色ID下的所有权限
        $roleData = RoleToAction::find()->where(['rid'=>$userInfo['role_id']])->asArray()->all();
        if(!empty($roleData)){
            $rid = array_column($roleData,"aid");
            $where[]=['in','id',$rid];
        }
        $where[]=['pid'=>$param['aid']];
        $data = Action::find()->select("id,name")->where($where)->asArray()->all();
        return $this->jsonSuccess("ok",$data);
    }

    /**
     * 设置常用模块
     */
    public function actionSetModules(){
        $param = Yii::$app->request->post();
        $userInfo = $this->getUserInfoByToken();
        $model = UserHomeConfig::findOne(['user_id'=>$userInfo['id']]);
        if(!empty($model)){
            $model->action_id = json_encode(array_values($param['action_id']));
            if($model->save(false)){
                return $this->jsonSuccess("设置成功");
            }else{
                return $this->jsonError("设置失败");
            }
        }else{
            $data = [
                'user_id'=>$userInfo['id'],
                'action_id'=>json_encode(array_values($param['action_id']))
            ];
            $model = new UserHomeConfig();
            if($model->load($data,'') && $model->save()){
                return $this->jsonSuccess("设置成功");
            }else{
                return $this->jsonError("设置失败");
            }
        }
    }

    /**
     * 获取常用模块
     */
    public function actionGetModules(){
        $userInfo = $this->getUserInfoByToken();
        $model = UserHomeConfig::findOne(['user_id'=>$userInfo['id']]);
        if(!empty($model)){
            $data = $model->toArray();
            $data['action_id'] = json_decode($data['action_id'],true);
            return $this->jsonSuccess("ok",$data['action_id']);
        }else{
            return $this->jsonSuccess("ok",[]);
        }
    }

    /**
     * 生成验证码(刷新时拼接&refresh=1)
     * author: lijin
     * @return string
     * @throws \Exception
     */
    public function actionCaptcha(){
        return CaptchaAction::run();
//        return Captcha::widget();
//        return Captcha::widget(['name'=>'captchaimg','captchaAction'=>'captcha','imageOptions'=>['id'=>'captchaimg', 'title'=>'换一个', 'alt'=>'换一个', 'style'=>'cursor:pointer;margin-top:10px; height: 22px;'],'template'=>'{image}']);
    }

}
