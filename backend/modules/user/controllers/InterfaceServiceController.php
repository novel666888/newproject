<?php

namespace backend\modules\user\controllers;

use common\models\InterfaceService;
use common\services\traits\ModelTrait;
use Yii;
use common\models\Action;
use common\models\Role;
use common\controllers\BaseController;


class InterfaceServiceController extends BaseController
{
    
    /**
     *  添加接口
     * @return false|string
     */
    public function actionCreate()
    {
        $param =  Yii::$app->request->post();
        $model = new InterfaceService();
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save()){
            return $this->jsonSuccess("创建成功");
        }else{
            return $this->jsonError("创建失败");
        }
    }

    /**
     * 修改接口
     * @return string
     */
    public function actionUpdate()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入接口id！");
        }
        $model = InterfaceService::findOne($param['id']);
        if(!$model){
            return $this->jsonError("接口不存在！");
        }
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save()){
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
        $model = InterfaceService::findOne($param['id']);
        if(!$model){
            return $this->jsonError("接口不存在！");
        }
        $isDelIntser = (new Action())->delRelation($model->id,2);
        if($isDelIntser && $model->delete()){
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
        $where=['and'];
        if(isset($param['interface_name']) && !empty($param['interface_name'])){
            $interface_name=trim($param['interface_name']);
            $where[]=['like','interface_name',$interface_name];
        }
        if(isset($param['path']) && !empty($param['path'])){
            $interface_path=trim($param['path']);
            $where[]=['like','interface_path',$interface_path];
        }
        if(!isset($param['page'])){
            $param['page'] = 1;
        }
        if(!isset($param['pageSize'])){
            $param['pageSize'] = ModelTrait::$defaultPageSize;
        }
        $model = InterfaceService::find()->where($where);
        $pagesOffset = ($param['page']-1)*$param['pageSize'];
        $interfaceInfo = $model->select('id,interface_name,interface_path,is_verification,create_time,update_time')->offset($pagesOffset)
            ->limit($param['pageSize'])
            ->asArray()
            ->all();
        $count = InterfaceService::find()->where($where)->count();
        $data = [
            'list'=>$interfaceInfo,
            'count'=>$count,
            'page'=>$param['page'],
            'pageSize'=>$param['pageSize'],
            'pageCount'=>ceil($count/$param['pageSize']),
        ];
        return $this->jsonSuccess('获取成功！',$data);
    }

    /**
     *   下拉框数据 All角色
     */
    public function actionInterfaceOption(){
        $param =  Yii::$app->request->post();
        $where=['and'];
        $where[]=['is_verification'=>0]; //只查询需要验证的接口
        if(isset($param['interface_name']) && !empty($param['interface_name'])){
            $interface_name=trim($param['interface_name']);
            $where[]=['like','interface_name',$interface_name];
        }

        $data = InterfaceService::find()->select("id,interface_name")->where($where)->asArray()->all();
        return $this->jsonSuccess("获取成功",$data);
    }

}
