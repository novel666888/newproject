<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2020/10/10
 * Time: 11:02
 */
namespace common\services;

use common\lib\Common;
use common\lib\Constant;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\httpclient\Client;
use yii\base\Component;

class HttpClient extends Component
{
    public $server;
    /**
     * @var \yii\httpclient\Client $client
     */
    public $client;

    private $i18nCategory = 'sys_error';

    /**
     * HttpClient constructor.
     * @param array $config
     * @throws InvalidConfigException
     */

    public function __construct(array $config = [])
    {
        if (!isset($config['server'])) {
            throw new InvalidConfigException('cannot find parameters `server`');
        }
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        $this->client = new Client();
    }

    /**
     * @param $methodPath
     * @param array $data
     * @param string $format
     * @param array $options
     * @return array|mixed
     * @throws InvalidConfigException
     * @throws UserException
     * @throws \yii\httpclient\Exception
     */
    public function post($methodPath,array $data=[], $format = Client::FORMAT_JSON, $options = ['timeout'=>10],$headers=[])
    {
        if(empty($methodPath)){
            \Yii::info(\Yii::t($this->i18nCategory, Constant::ERROR_CODE_SERVICE_API_METHOD_NOT_EXIST), 'process');
            throw new UserException(\Yii::t($this->i18nCategory, Constant::ERROR_CODE_SERVICE_API_METHOD_NOT_EXIST), Constant::ERROR_CODE_SERVICE_API_METHOD_NOT_EXIST);
        }
        if(empty($headers)){
            $headers = [
                "authorization:"."",
                "Content-Type:application/json",
                "Access-Token:".""
            ];
        }
        $request = $this->client->createRequest()
            ->setHeaders($headers)
            ->setOptions($options)
            ->setFormat($format)
            ->setMethod('post')
            ->setUrl($this->server.$methodPath)
            ->setData($data);
        $response = $request->send();
        /**@var \yii\httpclient\Response $response */
        if(!$response->getIsOk()){
            throw new \RuntimeException('Service API error!');
        }
        $res_data = $response->getData();
        if($res_data['code'] != 0){
            Common::adminLog($this->server.$methodPath, ['request_data' => $data, 'result' => $res_data], 'service-result-post');
        }
        return $res_data;
    }

    /**
     * @param $methodPath
     * @param array $data
     * @param $responseType
     * @param $token
     * @param string $format
     * @return array|mixed|string
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function get($methodPath, array $data=[], $responseType, $token, $format = Client::FORMAT_URLENCODED, $headers=[])
    {
        $methodPath = ltrim($methodPath,'/');
        if(empty($headers)){
            $headers = [
                "authorization:"."",
                "Content-Type:application/json",
                "Access-Token:".$token,
                "X-Debug-Mode:".""
            ];
        }
        $request = $this->client->createRequest()
            ->setHeaders($headers)
            ->setFormat($format)
            ->setMethod('get')
            ->setUrl($this->server.$methodPath)
            ->setData($data);
        $response = $request->send();
        /**@var \yii\httpclient\Response $response */
        if(!$response->getIsOk()){
            throw new \RuntimeException('service API error!');
        }
        if($responseType==1){
            $res_data = $response->content;
        }else{
            $res_data = $response->getData();
        }
        if ($res_data['code'] != 0){
            Common::adminLog($this->server.$methodPath, ['request_data' => $data, 'result' => $res_data], 'service-result-get');
        }
        return $res_data;
    }


}
