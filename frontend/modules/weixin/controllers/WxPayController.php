<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/10
 * Time: 10:30
 */
namespace frontend\modules\weixin\controllers;

use frontend\logic\NotifyLogic;
use frontend\logic\PayLogic;
use common\controllers\BaseController;

class WxPayController extends BaseController{

    public function actionPay(){
        $request = $this->getRequest();
        $order_id = $request->post('orderId');
        $pay = new PayLogic();
        $pay->payOrder($order_id);
    }

    public function actionNotify(){
        $request = $this->getRequest();
        $params = json_decode($request->post(), true);
        $order = new NotifyLogic();
        $order->order($params);
    }

}