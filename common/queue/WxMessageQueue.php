<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/4/8
 * Time: 17:08
 */
namespace common\queue;

use common\lib\Common;
use common\models\WxAccount;
use common\models\WxSendLog;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class WxMessageQueue extends BaseObject implements JobInterface
{
    public $wId;
    public $wcId;
    public $chatRoom;
    public $authorization;
    public $content;
    public function execute($queue)
    {
        $header = [
            "Content-Type:application/json",
            "Authorization:" . $this->authorization
        ];
        $data = [
            'wId' => $this->wId,
            'wcId' => $this->chatRoom['chatrooms'],
            'content' => $this->content
        ];
        $response = Common::OwenHttpRequest(WX_MSG_URL . 'sendImage', json_encode($data), 1, $header);
        Common::adminLog($data, $response, 'sendImage');
        $result = json_decode($response, 1);
        //发送失败，查看是否掉线
        if (isset($result['code']) && $result['code'] != "1000") {
            $check_login = [
                'wId' => $this->wId,
            ];
            $response_login_check = Common::OwenHttpRequest(WX_MSG_URL . 'isOnline', json_encode($check_login), 1, $header);
            Common::adminLog($check_login, $response_login_check, 'isOnline');
            $result_login_check = json_decode($response_login_check, 1);
            if (!$result_login_check['data']['isOnline']){//掉线进行重新登录
                $login_data = [
                    'wcId' => $this->wcId,
                    'type' => 2
                ];
                $response_login = Common::OwenHttpRequest(WX_MSG_URL . 'secondLogin', json_encode($login_data), 1, $header);
                Common::adminLog($login_data, $response_login, 'secondLogin');
                $result_login = json_decode($response_login, 1);
                if ($result_login['code'] == "1000") {//登录成功重新赋值更新wId
                    WxAccount::updateAll(['wid' => $result_login['data']['wId']], ['wid' => $this->wId]);
                }
            }
        }
        if (isset($result['code'])){
            if (isset($this->chatRoom['log_id']) && $result['code'] == "1000"){//check出来重新发送的，更新状态
                WxSendLog::updateAll(['result' => 1, 'msg' => '发送成功'], ['id' => $this->chatRoom['log_id']]);
            }else{
                $ins_data = [
                    'relation_id' => $this->chatRoom['id'],
                    'chatrooms' => $this->chatRoom['chatrooms'],
                    'room_name' => $this->chatRoom['room_name'],
                    'url' => $this->content,
                    'result'=> $result['code']=="1000" ? 1 : 0,
                    'msg'=> $result['message'],
                    'mark' => $this->chatRoom['mark'],
                ];
                $wxSendLog = new WxSendLog();
                $wxSendLog->setAttributes($ins_data);
                $wxSendLog->save();
            }
        }
        sleep(2);
        return 1;
    }
}