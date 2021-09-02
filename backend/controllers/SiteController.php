<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Dybaseapi\MNS\Requests\BatchReceiveMessage;
use AlibabaCloud\Dybaseapi\MNS\Requests\BatchDeleteMessage;
/**
 * Site controller
 */
class SiteController extends Controller
{


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionTest(){
        echo 1;die;

    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        AlibabaCloud::accessKeyClient('LTAIdAXnj4p76huF', 'm4i9vY1bvhmpkskVVmNb67RlC1Z5lj')
            ->regionId('cn-hangzhou')
            ->asGlobalClient();

        $queueName = 'Alicom-Queue-1915542789804374-VoiceReport'; // 队列名称
        $messageType = 'VoiceReport'; // 需要接收的消息类型

        $response = null;
        $token = null;
        $i = 0;

        do {
            try {
                if (null == $token || strtotime($token['ExpireTime']) - time() > 2 * 60) {
                    $response = AlibabaCloud::rpcRequest()
                        ->product('Dybaseapi')
                        ->version('2017-05-25')
                        ->action('QueryTokenForMnsQueue')
                        ->method('POST')
                        ->host("dybaseapi.aliyuncs.com")
                        ->options([
                            'query' => [
                                'MessageType' => $messageType,
                                'QueueName' => $queueName,
                            ],
                        ])
                        ->request()
                        ->toArray();
                }

                $token = $response['MessageTokenDTO'];

                $mnsClient = new \AlibabaCloud\Dybaseapi\MNS\MnsClient(
                    "http://1943695596114318.mns.cn-hangzhou.aliyuncs.com",
                    $token['AccessKeyId'],
                    $token['AccessKeySecret'],
                    $token['SecurityToken']
                );
                $mnsRequest = new BatchReceiveMessage(10, 5);
                $mnsRequest->setQueueName($queueName);
                $mnsResponse = $mnsClient->sendRequest($mnsRequest);

                $receiptHandles = Array();
                foreach ($mnsResponse->Message as $message) {
                    // 用户逻辑：
//                    $receiptHandles[] = $message->ReceiptHandle; // 加入$receiptHandles数组中的记录将会被删除
                    $messageBody = base64_decode($message->MessageBody); // base64解码后的JSON字符串
                    print_r($messageBody . "\n");
                }

                if (count($receiptHandles) > 0) {
                    $deleteRequest = new BatchDeleteMessage($queueName, $receiptHandles);
                    $mnsClient->sendRequest($deleteRequest);
                }
            } catch (ClientException $e) {
                echo $e->getErrorMessage() . PHP_EOL;
            } catch (ServerException $e) {
                if ($e->getCode() == 404) {
                    $i++;
                }
                echo $e->getErrorMessage() . PHP_EOL;
            }
        } while ($i < 3);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
