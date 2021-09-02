<?php

namespace backend\modules\user\controllers;

use common\models\Notify;
use \common\controllers\BaseController;
use common\models\NotifyUser;
use common\services\traits\ModelTrait;
use Yii;

class NotifyController extends BaseController
{
    /**
     *  添加通知
     * @return false|string
     */
    public function actionCreate()
    {
        $param =  Yii::$app->request->post();
        $model = new Notify();
        $model->load($param,'');
        if(!$model->validate()){
            return $this->jsonError($this->errorInfo($model->getFirstErrors()));
        }
        if($model->save()){
            return $this->jsonSuccess("通知成功!");
        }else{
            return $this->jsonError("通知失败");
        }
    }

    /**
     * 修改通知
     * @return string
     */
    public function actionUpdate()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入通知id！");
        }        $model = Notify::findOne($param['id']);
        if(!$model){
            return $this->jsonError("该通知不存在！");
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
     * 删除通知
     * @return string
     */
    public function actionDelete()
    {
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->jsonError("请输入通知id！");
        }
        $transaction = Yii::$app->db->beginTransaction(); //开启事务
        try {
            $model = Notify::findOne($param['id']);
            if(!$model){
                return $this->jsonError("通知不存在！");
            }
            $status = NotifyUser::deleteAll(['nid'=>$param['id']]);
            if($model->delete() && is_numeric($status)){
                $transaction->commit();
                return $this->jsonSuccess("删除成功");
            }else{
                $transaction->rollBack();
                return $this->jsonSuccess("删除失败");
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            return $this->jsonSuccess($e->getMessage());
        }
    }

    /**
     * 查询所有通知
     * @return string
     */
    public function actionSelect()
    {
        $param =  Yii::$app->request->post();
        $where=[];
        if(!isset($param['page'])){
            $param['page'] = 1;
        }
        if(!isset($param['pageSize'])){
            $param['pageSize'] = ModelTrait::$defaultPageSize;;
        }
        $model = Notify::find()->where($where);
        $pagesOffset = ($param['page']-1)*$param['pageSize'];
        $notifyInfo = $model->offset($pagesOffset)
            ->orderBy(['create_time'=>SORT_DESC])
            ->limit($param['pageSize'])
            ->asArray()
            ->all();
        $unread=0;
        if(isset($param['type']) && $param['type']==1){
            $uid = $this->getUidByToken();
            foreach($notifyInfo as $k=>$v){
                $notifyInfo[$k]['is_read'] = (new NotifyUser())->isRead($v['id'],$uid);
                $unread = $unread + $notifyInfo[$k]['is_read'];
            }
        }

        $count = Notify::find()->where($where)->count();
        $data = [
            'list'=>$notifyInfo,
            'count'=>$count,
            'unread'=>$unread,
            'page'=>$param['page'],
            'pageSize'=>$param['pageSize'],
            'pageCount'=>ceil($count/$param['pageSize']),
        ];
        return $this->jsonSuccess('获取成功！',$data);
    }

    /**
     * 阅读过通知设置已读
     * @return false|string
     */
    public function actionReadNotify(){
        $param =  Yii::$app->request->post();
        if(!isset($param['id']) || empty($param)){
              return $this->jsonError("请输入通知ID");
        }
        try {
            $model = new NotifyUser();
            $user_id= $this->getUidByToken();
            $notifyUser=[
                'user_id'=>$user_id,
                'nid'=>$param['id'],
                'read_time'=>date("Y-m-d H:i:s")
            ];
            if($model->load($notifyUser,'') && $model->save()){
                return $this->jsonSuccess("ok");
            }else{
                return $this->jsonError($this->errorInfo($model->getFirstErrors()));
            }
        }catch (\Exception $e){
            return $this->jsonError($e->getMessage());
        }
    }

}
