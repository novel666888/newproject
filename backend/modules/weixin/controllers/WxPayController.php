<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/10
 * Time: 10:30
 */
namespace backend\modules\weixin\controllers;

use backend\logic\PayLogic;
use common\controllers\BaseController;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

class WxPayController extends BaseController{

    public function actionWxPay(){
        $request = $this->getRequest();
        $order_id = $request->post('orderId');
        $pay = new PayLogic();
        $pay->payOrder($order_id);
    }


    public function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

}