<?php

namespace backend\modules\user\controllers;
use backend\logic\organizeLogic;
use common\models\Organize;
use Yii;
use common\controllers\BaseController;
use yii\db\Exception;


class OrganizeController extends BaseController
{

    public $errCode=[-1=>"删除失败，请重试！",1=>"该架构下存在子架构，请转移或者删除之后再进行该操作！",2=>"该架构下存在员工，请转移员工之后再进行删除"];
    /**
     * 创建组织架构
     * @return string
     */
    public function actionCreate()
    {
        $param =  Yii::$app->request->post();
        $model  = new Organize();
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save()){
            return $this->jsonSuccess("创建成功");
        }else{
            return $this->jsonError("创建失败",$model->errors);
        }
    }

    /**
     * 修改组织架构
     * @return string
     */
    public function actionUpdate()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id'])){
            return $this->jsonError("缺少参数id");
        }
        if(!isset($param['organize_name'])){
            return $this->jsonError("缺少参数organize_name");
        }
        if(!isset($param['pid'])){
            return $this->jsonError("缺少参数pid");
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = Organize::findOne($param['id']);
            $model->organize_name=$param['organize_name'];
            $model->pid=$param['pid'];
            if($model->save()){
                $transaction->commit();
                return $this->jsonSuccess("修改成功");
            }else{
                $transaction->rollBack();
                return $this->jsonError("修改失败",$model->getErrors());
            }

        } catch (\Exception $e){
            $transaction->rollBack();
            return $this->jsonError("修改失败",$e->errorInfo);
        }
    }

    /**
     * 查询组织架构
     * @return string
     */
    public function actionSelect()
    {
        $data= (new Organize())->getOrganizeData();
        return $this->jsonSuccess("获取成功",$data);
    }

    /**
     * 删除组织架构
     * @return string
     */
    public function actionDelete()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id'])){
            return $this->jsonError("缺少参数id",[],400);
        }
        $status = (new Organize())->delOrganizeForId($param['id']);
        if($status==0){
            return $this->jsonSuccess("删除成功");
        }else{
            return $this->jsonError($this->errCode[$status]);
        }
    }


    /**
     * 组织架构详情
     * @return string
     */
    public function actionInfo()
    {
        $param = Yii::$app->request->post();
        if(!isset($param['id'])){
             return $this->jsonError("参数错误");
        }
        $data = Organize::find()->where(['id'=>$param['id']])->asArray()->one();
        return $this->jsonSuccess("获取成功",$data);
    }


    /**
     *   下拉框数据 All部门
     */
    public function actionOrganizeOption(){
        $param=Yii::$app->request->post();
        if(!isset($param['type'])){
            $param['type']=1;
        }
        if($param['type']==1){ //获取所有逇组织架构 id  及 名称
            $data = Organize::find()->select("id,organize_name")->asArray()->all();
            return $this->jsonSuccess("获取成功！",$data);
        }elseif($param['type']==2){
            if(!isset($param['id'])){ //只返回部门
                $data = Organize::find()->select("id,organize_name")->where(['level'=>2])->asArray()->all();
                return $this->jsonSuccess("获取成功！",$data);
            }else{
                $data = Organize::find()->select("id,organize_name")->where(['pid'=>$param['id']])->asArray()->all();
                return $this->jsonSuccess("获取成功！",$data);
            }
        }
    }


}
