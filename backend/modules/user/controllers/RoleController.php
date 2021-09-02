<?php

namespace backend\modules\user\controllers;

use common\models\Action;
use common\models\Role;
use \common\controllers\BaseController;
use common\models\RoleToAction;
use common\models\Users;
use common\services\traits\ModelTrait;
use Yii;
use yii\web\User;

class RoleController extends BaseController
{
    /**
     *  添加角色
     * @return false|string
     */
    public function actionCreate()
    {
        $param =  Yii::$app->request->post();
        $model = new Role();
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        $aids = $param['aids'];
        unset($param['aids']);
        if($model->save()){
            $insert_id = $model->attributes['id'];
            (new Role())->addRelation($insert_id,$aids);
            return $this->jsonSuccess("创建成功",['id'=>$insert_id]);
        }else{
            return $this->jsonError("创建失败");
        }
    }

    /**
     * 修改角色
     * @return string
     */
    public function actionUpdate()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入角色id！");
        }
        $model = Role::findOne($param['id']);
        if(!$model){
            return $this->jsonError("角色不存在！");
        }
        $aids = $param['aids'];
        unset($param['aids']);
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save()){
            (new Role())->addRelation($param['id'],$aids);
            Users::updateAll(['role_name'=>$param['role_name']],['role_id'=>$model->id]);
            return $this->jsonSuccess("修改成功");
        }else{
            return $this->jsonError("修改失败");
        }
    }

    /**
     * 删除角色 该角色下面绑定了员工的话，无法被删除
     * @return string
     */
    public function actionDelete()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入角色id！");
        }
        $model = Role::findOne($param['id']);
        if(!$model){
            return $this->jsonError("角色不存在！");
        }
        if($model->delete()){
            (new Role())->delRelation($param['id']);
            return $this->jsonSuccess("删除成功");
        }else{
            return $this->jsonSuccess("删除失败");
        }
    }

    /**
     * 查询所有角色
     * @return string
     */
    public function actionSelect()
    {
        $param =  Yii::$app->request->post();
        $where=[];
        if(isset($param['role_name']) && !empty($param['role_name'])){
            $role_name=trim($param['role_name']);
            $where=['like','role_name',$role_name];
        }
        if(!isset($param['page'])){
            $param['page'] = 1;
        }
        if(!isset($param['pageSize'])){
            $param['pageSize'] = ModelTrait::$defaultPageSize;;
        }
        $model = Role::find()->where($where);
        $pagesOffset = ($param['page']-1)*$param['pageSize'];
        $roleInfo = $model->select('id,role_name,sort')->offset($pagesOffset)
            ->orderBy(['sort'=>SORT_ASC])
            ->limit($param['pageSize'])
            ->asArray()
            ->all();
        foreach($roleInfo as $k=>$v){
             $roleInfo[$k]['bindUserCnt'] = (int)(new Users())->getUserByRoleId($v['id']);
        }
        $count = Role::find()->where($where)->count();
        $data = [
            'list'=>$roleInfo,
            'count'=>$count,
            'page'=>$param['page'],
            'pageSize'=>$param['pageSize'],
            'pageCount'=>ceil($count/$param['pageSize']),
        ];
        return $this->jsonSuccess('获取成功！',$data);
    }

    /**
     * 获取角色详情
     */
    public function actionRoleInfo(){
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("缺少角色ID",[],400);
        }
        $data  =  Role::find()->where(['id'=>$param['id']])->asArray()->one();
        //获取当前角色下绑定的菜单
        $roleToAction=RoleToAction::find()->select('aid')->where(['rid'=>$param['id']])->asArray()->all();
        if(empty($roleToAction)){
            $data["aids"] =[];
        }else{
            //过滤掉一级菜单
            $aData = Action::find()->where(['and',['in','id',array_column($roleToAction,'aid')],['action_type'=>3]])->asArray()->all();
            $data["aids"] =array_column($aData,'id');
        }
        $data["actionInfo"]=(new Role())->getMenuByRid($data['id']);
        return $this->jsonSuccess("查询成功",$data);
    }
    /**
     *   下拉框数据 All角色
     */
    public function actionRoleOption(){
        $data = Role::find()->select("id,role_name")->asArray()->all();
        return $this->jsonSuccess("获取成功",$data);
    }

}
