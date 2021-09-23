<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/13
 * Time: 15:13
 */
namespace frontend\logic;

use common\lib\Common;
use WeChatPay\Crypto\AesGcm;
use yii\base\Exception;

class NotifyLogic{

    private $aesKey;
    const KEY_LENGTH_BYTE = 32;

    public function __construct(){
        $wxConfig = \Yii::$app->params['wxConfig'];
        if (strlen($wxConfig['v3Key']) != self::KEY_LENGTH_BYTE){
            Common::adminLog('无效的v3Key', ['v3Key' => $wxConfig['v3Key']], 'v3Key_error');
            throw new Exception('无效的ApiV3Key，长度应为32个字节');
        }
        $this->aesKey = $wxConfig['v3Key'];
    }

    /**
     * author: lijin
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function order($data){
        try{
            $decrypt_data = AesGcm::decrypt($data['resource']['ciphertext'], $this->aesKey, $data['resource']['nonce'], $data['resource']['associated_data']);
            $this->deal(json_decode($decrypt_data, true));
        }catch (\RuntimeException $re){
            Common::adminLog($re->getMessage(), $data, 'runtime_error');
        }catch (\UnexpectedValueException $ue){
            Common::adminLog($ue->getMessage(), $data, 'unexpected_error');
        }
        return true;
    }

    /**
     * 处理订单
     * author: lijin
     * @param $order
     */
    private function deal($order){

    }

}