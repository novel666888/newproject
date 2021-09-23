<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/9/13
 * Time: 19:11
 */
class DemoController
{
    const AUTH_TAG_LENGTH_BYTE = 16;

    //回调地址
    public function notifyUrl()
    {
        $header = $this->getHeaders();//读取http头信息
        $body = file_get_contents('php://input');//读取微信传过来的信息，是一个json字符串

        if (empty($header) || empty($body)) {
            throw new Exception('通知参数为空', 2001);
        }

        $timestamp = $header['WECHATPAY-TIMESTAMP'];
        $nonce = $header['WECHATPAY-NONCE'];
        $signature = $header['WECHATPAY-SIGNATURE'];
        $serialNo = $header['WECHATPAY-SERIAL'];
        if (empty($timestamp) || empty($nonce) || empty($signature) || empty($serialNo)) {
            throw new Exception('通知头参数为空', 2002);
        }
        $cert = $this->getzhengshuDb();
        if ($cert != $serialNo) {
            throw new Exception('验签失败', 2005);
        }
        $message = "$timestamp\n$nonce\n$body\n";

        //校验签名
        if (!$this->verify($message, $signature, 'E:\项目\cert\cert.pem')) {//E:\项目\cert\cert.pem是获取平台证书序列号getzhengshuDb()时保存下来的平台公钥
            throw new Exception('验签失败', 2005);
        }

        $decodeBody = json_decode($body, true);
        if (empty($decodeBody) || !isset($decodeBody['resource'])) {
            throw new Exception('通知参数内容为空', 2003);
        }
        $decodeBodyResource = $decodeBody['resource'];
        $decodeData = $this->decryptToStringHd($decodeBodyResource['associated_data'], $decodeBodyResource['nonce'], $decodeBodyResource['ciphertext'], '');//解密resource
//        $decodeData = \WeChatPay\Crypto\AesGcm::decrypt($decodeBodyResource['ciphertext'], '', $decodeBodyResource['nonce'], $decodeBodyResource['associated_data']);//解密resource
        $decodeData = json_decode($decodeData, true);
        foreach ($decodeData['sub_orders'] as $val) {//循环解密出来的子订单
            if ($val['trade_state'] == "SUCCESS") {//判断是否支付成功
                $val['out_trade_no'];//是商户的自定义订单号
                $val['transaction_id'];//微信的支付单号
            }
        }
        $arr = array("code" => "SUCCESS", "message" => "");
        echo json_encode($arr);
        exit;
    }

    //获取微信回调http头信息
    public function getHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
            if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $header['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
            } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                $header['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
            }
            if (isset($_SERVER['CONTENT_LENGTH'])) {
                $header['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
            }
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $header['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
            }
        }
        return $headers;
    }

    //获取平台证书序列号
    public function getzhengshuDb()
    {
        $url = "https://api.mch.weixin.qq.com/v3/certificates";
        $timestamp = time();//时间戳
        $nonce = $this->nonce_str();//获取一个随机数
        $body = "";
        $mch_private_key = $this->getPublicKey();//读取商户api证书公钥
        $merchant_id = '1234567890';//服务商商户号
        $serial_no = '商户证书序列号';//在API安全中获取
        $sign = $this->sign($url, 'GET', $timestamp, $nonce, $body, $mch_private_key, $merchant_id, $serial_no);//签名

        $header = [
            'Authorization:WECHATPAY2-SHA256-RSA2048 ' . $sign,
            'Accept:application/json',
            'User-Agent:' . $merchant_id
        ];
        $result = $this->curl($url, '', $header, 'GET');
        $result = json_decode($result, true);
        $serial_no = $result['data'][0]['serial_no'];
        $encrypt_certificate = $result['data'][0]['encrypt_certificate'];
        $sign_key = "32位微信v3商户证书密钥";//在API安全中设置
        $result = $this->decryptToString($encrypt_certificate['associated_data'], $encrypt_certificate['nonce'], $encrypt_certificate['ciphertext'], $sign_key);//解密
        file_put_contents('E:\项目\cert\cert.pem', $result);
        return $serial_no;
    }

    //生成随机字符串
    public function nonce_str($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //读取商户api证书公钥
    public function getPublicKey()
    {
        return openssl_get_privatekey(file_get_contents('E:\项目\cert\apiclient_key.pem'));//微信商户平台中下载下来，保存到服务器直接读取
    }

    //签名
    public function sign($url, $http_method, $timestamp, $nonce, $body, $mch_private_key, $merchant_id, $serial_no)
    {
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $message =
            $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf(
            'mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"',
            $merchant_id,
            $nonce,
            $sign,
            $timestamp,
            $serial_no
        );
        return $token;
    }

    //curl提交
    public function curl($url, $data = [], $header, $method = 'POST')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    //解密返回的信息
    public function decryptToString($associatedData, $nonceStr, $ciphertext, $aesKey)
    {
        $ciphertext = \base64_decode($ciphertext);
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -16);
            $authTag = substr($ciphertext, -16);
            return \openssl_decrypt(
                $ctext,
                'aes-256-gcm',
                $aesKey,
                \OPENSSL_RAW_DATA,
                $nonceStr,
                $authTag,
                $associatedData
            );
        }
        throw new \RuntimeException('php7.1');
    }

    //信息解密
    private function decryptToStringHd($associatedData, $nonceStr, $ciphertext, $aesKey = '')
    {
        if (empty($aesKey)) {
            $aesKey = "微信证书32位v3密钥";//微信商户平台 api安全中设置获取
        }
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }
        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') &&
            \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') &&
            \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

            return \openssl_decrypt($ctext, 'aes-256-gcm', $aesKey, \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }

        throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }

    //签名验证操作
    private function verify($message, $signature, $merchantPublicKey)
    {
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new \RuntimeException("当前PHP环境不支持SHA256withRSA");
        }
        $signature = base64_decode($signature);
        $a = openssl_verify($message, $signature, $this->getWxPublicKey($merchantPublicKey), 'sha256WithRSAEncryption');
        return $a;
    }

    //获取平台公钥  获取平台证书序列号时存起来的cert.pem文件
    protected function getWxPublicKey($key)
    {
        $public_content = file_get_contents($key);
        $a = openssl_get_publickey($public_content);
        return $a;
    }
}