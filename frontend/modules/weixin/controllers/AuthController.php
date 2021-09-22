<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/2
 * Time: 10:35
 */
namespace frontend\modules\weixin\controllers;

use frontend\logic\AuthLogic;
use common\controllers\BaseController;

class AuthController extends BaseController{

    public function actionAuth(){
        $request = $this->getRequest();
        $code = trim($request->post('code', 'code'));
        AuthLogic::code2Session($code);
    }
}