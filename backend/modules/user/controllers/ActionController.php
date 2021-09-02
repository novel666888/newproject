<?php

namespace backend\modules\user\controllers;

use common\models\Action;
use common\models\Organize;
use common\models\RoleToAction;
use Yii;

class ActionController extends \common\controllers\BaseController
{
    /**
     * 添加菜单
     * @return false|string
     */
    public function actionCreate()
    {
        $param =  Yii::$app->request->post();
        $model = new Action();
        $interIds=[];
        if(isset($param['interIds'])){
            if(!is_array($param['interIds'])){
                $param['interIds'] = explode(",",$param['interIds']);
            }
            $interIds = $param['interIds'];
            unset($param['interIds']);
        }
        $model->load($param,'');
        if(!$model->validate()){
             return $this->jsonError($this->errorInfo($model->getFirstErrors()),[],400);
        }

        if($model->save()){
            $insertId = $model->attributes['id'];
            if(!empty($interIds)){
                (new Action())->addRelations($insertId,$interIds);
            }
            return $this->jsonSuccess("创建成功");
        }else{
            return $this->jsonError("创建失败");
        }

    }
    /**
     * 修改菜单
     * @return false|string
     */
    public function actionUpdate()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入操作id！",[],400);
        }
        $model = Action::findOne($param['id']);
        if(!$model){
            return $this->jsonError("操作不存在",[],404);
        }
        $interIds=[];
        if(isset($param['interIds'])) {
            if (!is_array($param['interIds'])) {
                $param['interIds'] = explode(",", $param['interIds']);
            }
            $interIds = $param['interIds'];
            unset($param['id']);
            unset($param['interIds']);
        }

        $model->load($param,'');

        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()),[],400);
        }
        if($model->save()){
            if(!empty($interIds)){
                (new Action())->addRelations($model->id,$interIds);
            }
            return $this->jsonSuccess("修改成功");
        }else{
            return $this->jsonError("修改失败");
        }
    }

    /**
     * 删除菜单
     * @return false|string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id'])){
            return $this->jsonError("请输入操作id！",[],400);
        }
        $model = Action::findOne($param['id']);
        if(!$model){
            return $this->jsonError("操作不存在",[],404);
        }
        $isDelRole = (new RoleToAction())->delRelationByAid($model->id);
        $isDelIntser = (new Action())->delRelation($model->id,1);
        if($isDelRole && $isDelIntser && $model->delete()){
            return $this->jsonSuccess("删除成功");
        }else{
            return $this->jsonError("删除失败",$model->getFirstErrors());
        }
    }
    /**
     * 查询菜单菜单
     * @return false|string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionSelect()
    {
        $data = Action::find()->asArray()->all();
        foreach($data as $k=>$v){
            $data[$k]['interIds']=(new Action())->getBindInterface($v['id']);
        }
        $data = Organize::generateTree($data);
        return $this->jsonSuccess('获取成功！',$data);
    }

    public function actionInfo()
    {
        $param = Yii::$app->request->post();
        if(!isset($param['id'])){
              return $this->jsonError("请输入操作ID");
        }
        $data = Action::find()->where(['id'=>$param['id']])->asArray()->one();
        return $this->jsonSuccess('获取成功！',$data);
    }

    /**
     * 当前角色 可以掉用当前页面的哪些按钮
     */
    public function actionAuthButton(){
        $param = Yii::$app->request->post();
        if(!isset($param['id'])){
            return $this->jsonError("请输入二级操作操作ID");
        }
        //查询出匹配二级的按钮
        $data = Action::find()->select("id,soft")->where(['pid'=>$param['id']])->asArray()->all();
        $userInfo = $this->getUserInfoByToken();
        //查询出当前角色匹配的按钮级别操作
        $authData = (new RoleToAction())->getButtonByRid($userInfo['role_id'],$param['id']);
        $returnData=[];
        foreach($data as $k=>$v){
            if($userInfo['role_id']==1){
                $returnData[$v['soft']]=true;
            }else{
                if(in_array($v['id'],$authData)){
                    $returnData[$v['soft']]=true;
                }else{
                    $returnData[$v['soft']]=false;
                }
            }
        }
        $pathData =  Action::find()->select("id,path")->where(['id'=>$param['id']])->asArray()->one();
        return $this->jsonSuccess("获取成功",$returnData,200,['path'=>$pathData['path']]);
    }
}
