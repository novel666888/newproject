<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/10
 * Time: 15:16
 */
namespace backend\logic;

use common\lib\Common;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;
use yii\httpclient\Client;

class PayLogic{

    private $mch_private_key = '';
    private $mch_id = '';
    private $method = "POST";
    private $url = "https://api.mch.weixin.qq.com/v3/certificates";
    private $curl_timeout = 10;
    private $serial_no = '';

    /**
     * 支付
     * author: lijin
     * @param $order_id
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\httpclient\Exception
     */
    public function payOrder($order_id){
        $order_info = getOrder($order_id);
        $user_info = getUser($order_info['user_id']);
        $wxConfig = \Yii::$app->params['wxConfig'];
        //商户私钥，路径 `/path/to/merchant/apiclient_key.pem`
        $merchantPrivateKeyFilePath = 'file:///path/to/merchant/apiclient_key.pem';// 注意 `file://` 开头协议不能少
        //加载商户私钥
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
//        $merchantCertificateSerial = '可以从商户平台直接获取到';// API证书不重置，商户证书序列号就是个常量
//
//        //「平台证书」，可由下载器 `./bin/CertificateDownloader.php` 生成并假定保存为 `/path/to/wechatpay/cert.pem`
//        $platformCertificateFilePath = 'file:///path/to/wechatpay/cert.pem';// 注意 `file://` 开头协议不能少
//        //加载「平台证书」公钥
//        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
//        //解析「平台证书」序列号，「平台证书」当前五年一换，缓存后就是个常量
//        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
        $pay_config = [
            "mchid" => $wxConfig['mchId'],
            "out_trade_no" => $order_info['order_sn'],
            "appid" => $wxConfig['appId'],
            "description" => $order_info['goods_name'],
            "notify_url" => $wxConfig['notifyUrl'],
            "amount" => [
                "total" => $order_info['amount']*100,
                "currency" => "CNY"
            ],
            "payer"=> [
                "openid" => $user_info['openid']
            ]
        ];
        $header = $this->getAuth($pay_config, $wxConfig, $merchantPrivateKeyInstance);
        $prepay_id = $this->getPrepayId($pay_config, $wxConfig, $header);
        $pay_config = $this->getPayConfig($prepay_id, $wxConfig, $merchantPrivateKeyInstance);
        return $pay_config;
    }

    /**
     * 获取预支付id
     * author: lijin
     * @param $pay_config
     * @param $wxConfig
     * @param $header
     * @return mixed
     * @throws \yii\base\Exception
     * @throws \yii\httpclient\Exception
     */
    private function getPrepayId($pay_config, $wxConfig, $header){
        $client = new Client();
        $request = $client->post($wxConfig['prepayOrderUrl'], $pay_config, $header);
        $response = $request->send();
        /**@var \yii\httpclient\Response $response */
        if(!$response->getIsOk()){
            throw new \RuntimeException('Service API error!');
        }
        $result = $response->getData();
        if($result['code'] != 0){
            Common::adminLog($wxConfig['prepayOrderUrl'], ['request_data' => $pay_config, 'response' => $result], 'get-prepayId-error');
            throw new \yii\base\Exception('获取prepay_id失败');
        }
        return $result['prepay_id'];
    }

    /**
     * 获取支付信息
     * author: lijin
     * @param $prepay_id
     * @param $wxConfig
     * @param $merchantPrivateKeyInstance
     * @return array
     */
    private function getPayConfig($prepay_id, $wxConfig, $merchantPrivateKeyInstance){
        $app_id = $wxConfig['appId'];
        $time = time();
        $str = $this->createNoncestr();
        $package = 'prepay_id='.$prepay_id;
        $message = $app_id."\n".
            $time."\n".
            $str."\n".
            $prepay_id."\n";
        $sign = Rsa::sign($message, $merchantPrivateKeyInstance);
        return ['appId' => $app_id, 'timeStamp' => $time, 'nonceStr' => $str, 'package' => $package, 'signType' => 'RSA', 'paySign' => $sign];
    }

    /**
     * 获取请求头
     * author: lijin
     * @param $pay_config
     * @param $wxConfig
     * @param $merchantPrivateKeyInstance
     * @return array
     */
    private function getAuth($pay_config, $wxConfig, $merchantPrivateKeyInstance){
        $url_parts = parse_url($wxConfig['prepayOrderUrl']);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        //当前时间戳
        $timestamp = time();
        //随机字符串
        $nonce_str = $this->createNoncestr();
        //POST请求时 需要 转JSON字符串
        $body = json_encode($pay_config);
        $message = $this->method."\n".
            $canonical_url."\n".
            $timestamp."\n".
            $nonce_str."\n".
            $body."\n";
        //生成签名
        $sign = Rsa::sign($message, $merchantPrivateKeyInstance);
        //Authorization 类型
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        //生成认证信息
        $token = sprintf('mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"', $wxConfig['mchId'], $nonce_str, $sign, $timestamp, $wxConfig['serialNo']);
        $header = [
            'Content-Type:application/json',
            'Accept:application/json',
            'User-Agent:*/*',
            'Authorization: '.  $schema . ' ' . $token
        ];
        return $header;
    }

    /**
     * 获取证书
     */
    public function getCertificates(){
        //生成V3请求 header认证信息
        $header = $this->createAuthorization($this->url);
        $header[] = 'User-Agent : https://zh.wikipedia.org/wiki/User_agent';
        $data = $this->getXmlCurl($this->url, $this->curl_timeout , $header);
        return json_decode($data , true);
    }

    public function regguide( $post ,$serial_no){
        $url = "https://api.mch.weixin.qq.com/v3/smartguide/guides";
        $this->setBody( $post );
        //生成V3请求 header认证信息
        $header = $this->createAuthorization( $url , 'POST' );
        //增加平台证书序列号 ， 平台证书序列号方法 getcertificates()
        $header[] = 'Wechatpay-Serial:' . $serial_no;
        $data = $this->postXmlCurl(json_encode($post , JSON_UNESCAPED_UNICODE) ,  $url  , 30 , $header );
        return json_decode($data , true);
    }

    public function createNoncestr($length = 32){
        $chars = "abcdefghijklmnpqrstuvwxyz123456789";
        $str = "";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}