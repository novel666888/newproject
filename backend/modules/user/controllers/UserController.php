<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/9/29
 * Time: 17:38
 */
namespace backend\modules\user\controllers;
use Yii;
use common\controllers\BaseController;

class UserController extends BaseController {

    public function actionIndex(){
        if(Yii::$app->request->getIsGet()){
            $param  = Yii::$app->request->get();
        }else{
            $param  = Yii::$app->request->post();
        }
        return json_encode($param);
    }

}